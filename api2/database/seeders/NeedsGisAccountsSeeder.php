<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Governorate;
use App\Models\Need;
use App\Models\User;
use App\Support\BranchDataScope;
use App\Support\NeedStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

/**
 * يربط حسابات GIS (محافظ / مدير فرع / إدخال / تدقيق) بالمحافظات الـ14
 * ويُنشئ حسابات تجريبية @system.com للاختبار.
 *
 * php artisan db:seed --class=NeedsGisAccountsSeeder
 */
class NeedsGisAccountsSeeder extends Seeder
{
    private const DEMO_PASSWORD = '12345678';

    /** @var array<string, list<string>> governorate code => email fragments */
    private const EMAIL_FRAGMENTS = [
        'damascus' => ['damascus'],
        'rural_damascus' => ['rif-dimashq', 'rif.damascus', 'rural.damascus', 'rural_damascus', 'rif_dimashq'],
        'aleppo' => ['aleppo'],
        'homs' => ['homs'],
        'hama' => ['hama'],
        'latakia' => ['latakia', 'lattakia'],
        'tartus' => ['tartus', 'tartous'],
        'idlib' => ['idlib'],
        'daraa' => ['daraa'],
        'suweida' => ['suwayda', 'suweida'],
        'quneitra' => ['quneitra'],
        'deir_ez_zor' => ['deir-ez-zor', 'deir.ezzor', 'deir_ezzor', 'deir_ez_zor', 'deir-ezzor'],
        'raqqa' => ['raqqa'],
        'hasakah' => ['hasakah'],
    ];

    /** @var list<string> */
    private const SCOPED_ROLES = ['branch_manager', 'governor', 'data_entry', 'data_reviewer'];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $branches = Branch::query()
            ->with('governorate:id,code,name_ar')
            ->where('is_active', true)
            ->get()
            ->keyBy(fn (Branch $b) => $b->governorate?->code ?? '');

        $stats = [
            'backfilled' => 0,
            'created' => 0,
            'demo_needs' => 0,
        ];

        foreach (BranchDataScope::SYRIAN_GOVERNORATES as $govDef) {
            $code = $govDef['code'];
            $branch = $branches->get($code);
            if (!$branch) {
                $this->command?->warn("Branch missing for governorate: {$code}");

                continue;
            }

            $stats['backfilled'] += $this->backfillUsersForGovernorate($branch, $code);
            $stats['created'] += $this->ensureDemoAccounts($branch, $govDef['name_ar'], $code);
        }

        $stats['demo_needs'] = $this->ensureDemoPendingNeeds();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info(sprintf(
            'Needs GIS accounts: backfilled=%d, demo accounts touched=%d, demo pending needs=%d',
            $stats['backfilled'],
            $stats['created'],
            $stats['demo_needs'],
        ));
    }

    private function backfillUsersForGovernorate(Branch $branch, string $code): int
    {
        $fragments = self::EMAIL_FRAGMENTS[$code] ?? [$code];
        $count = 0;

        User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', self::SCOPED_ROLES))
            ->where(function ($q) {
                $q->whereNull('branch_id')->orWhereNull('governorate_id');
            })
            ->get()
            ->each(function (User $user) use ($branch, $fragments, &$count) {
                if (!$this->emailMatchesGovernorate($user->email, $fragments)) {
                    return;
                }

                $user->update([
                    'governorate_id' => $branch->governorate_id,
                    'branch_id' => $branch->id,
                ]);
                $count++;
            });

        return $count;
    }

    private function emailMatchesGovernorate(string $email, array $fragments): bool
    {
        $local = strtolower(strtok($email, '@') ?: $email);

        foreach ($fragments as $fragment) {
            if (str_contains($local, strtolower($fragment))) {
                return true;
            }
        }

        return false;
    }

    private function ensureDemoAccounts(Branch $branch, string $nameAr, string $code): int
    {
        $count = 0;
        $password = Hash::make(self::DEMO_PASSWORD);

        $accounts = [
            [
                'email' => "governor.{$code}@system.com",
                'name' => "محافظ {$nameAr}",
                'entity_type' => 'governor',
                'role' => 'governor',
            ],
            [
                'email' => "branch.{$code}@system.com",
                'name' => "مدير فرع {$nameAr}",
                'entity_type' => 'branch_manager',
                'role' => 'branch_manager',
            ],
            [
                'email' => "data-entry.{$code}@system.com",
                'name' => "مدخل بيانات {$nameAr}",
                'entity_type' => 'data_entry',
                'role' => 'data_entry',
            ],
            [
                'email' => "data-reviewer.{$code}@system.com",
                'name' => "مدقق بيانات {$nameAr}",
                'entity_type' => 'data_reviewer',
                'role' => 'data_reviewer',
            ],
        ];

        foreach ($accounts as $def) {
            $user = User::query()->firstOrNew(['email' => $def['email']]);

            if (!$user->exists) {
                $user->password = $password;
                $count++;
            }

            $user->fill([
                'name' => $def['name'],
                'entity_type' => $def['entity_type'],
                'governorate_id' => $branch->governorate_id,
                'branch_id' => $branch->id,
                'is_active' => true,
            ]);
            $user->save();
            $user->syncRoles([$def['role']]);
        }

        return $count;
    }

    private function ensureDemoPendingNeeds(): int
    {
        $created = 0;
        $demoCodes = ['tartus', 'damascus', 'aleppo'];

        foreach ($demoCodes as $code) {
            $gov = Governorate::query()->where('code', $code)->first();
            $branch = $gov
                ? Branch::query()->where('governorate_id', $gov->id)->where('is_active', true)->first()
                : null;

            if (!$gov || !$branch) {
                continue;
            }

            $marker = "DEMO-PENDING-{$code}";

            if (Need::query()->where('title', $marker)->exists()) {
                continue;
            }

            $entry = User::query()->where('email', "data-entry.{$code}@system.com")->first();
            if (!$entry) {
                continue;
            }

            Need::query()->create([
                'need_code' => 'NEED-DEMO-' . strtoupper(str_replace('_', '-', $code)) . '-' . now()->format('His'),
                'title' => $marker,
                'description' => 'احتياج تجريبي لاختبار مسار التدقيق والموافقة.',
                'need_owner_type' => 'citizen',
                'need_scope' => 'local',
                'sector' => 'تنمية محلية',
                'priority' => 'متوسطة',
                'source_platform' => 'manual',
                'governorate_id' => $gov->id,
                'branch_id' => $branch->id,
                'status' => NeedStatus::PENDING_GOVERNORATE_REVIEW,
                'approval_status' => NeedStatus::PENDING_GOVERNORATE_REVIEW,
                'latitude' => $this->demoLatitude($code),
                'longitude' => $this->demoLongitude($code),
                'is_mapped' => true,
                'created_by' => $entry->id,
                'updated_by' => $entry->id,
            ]);

            $created++;
        }

        return $created;
    }

    private function demoLatitude(string $code): float
    {
        return match ($code) {
            'damascus' => 33.513,
            'aleppo' => 36.202,
            'tartus' => 34.889,
            default => 35.0,
        };
    }

    private function demoLongitude(string $code): float
    {
        return match ($code) {
            'damascus' => 36.292,
            'aleppo' => 37.134,
            'tartus' => 35.887,
            default => 38.5,
        };
    }
}
