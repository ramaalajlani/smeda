<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * حسابات إدارة احتياجات محافظة حمص:
 *   - governor.homs@system.com  (محافظ حمص — دور governor)
 *   - branch.homs@system.com    (مدير فرع حمص — دور branch_manager)
 *
 * يعيد استخدام NeedsGisAccountsSeeder لإنشاء الحسابات وربطها بالنطاق
 * (governorate_id / branch_id)، ثم يمنح الحسابين صلاحيات إدارة الاحتياجات
 * كاملة كصلاحيات مباشرة على مستوى المستخدم فقط — دون أي تعديل على أدوار
 * governor أو branch_manager عالمياً، ودون منح needs.view_all إطلاقاً.
 *
 * حصر البيانات بمحافظة حمص مطبق في الـ Back-end عبر NeedDataScope
 * (المحافظ يُحصر بـ governorate_id ومدير الفرع بـ branch_id).
 *
 * php artisan db:seed --class=HomsNeedsAccountsSeeder
 */
class HomsNeedsAccountsSeeder extends Seeder
{
    public const HOMS_EMAILS = [
        'governor.homs@system.com',
        'branch.homs@system.com',
    ];

    /** @var list<string> صلاحيات إدارة احتياجات محافظة حمص — بدون needs.view_all عمداً */
    public const HOMS_NEEDS_PERMISSIONS = [
        'needs.view',
        'needs.view_branch',
        'needs.create',
        'needs.create_citizen',
        'needs.create_state',
        'needs.update',
        'needs.review',
        'needs.approve',
        'needs.reject',
        'needs.return',
        'needs.classify',
        'needs.resolve',
        'needs.export',
        'needs.dashboard',
        'needs.map',
    ];

    public function run(): void
    {
        // إنشاء/تحديث الحسابات وربط النطاق — إعادة استخدام المنطق الموجود
        $this->call(NeedsGisAccountsSeeder::class);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $branch = Branch::query()
            ->whereHas('governorate', fn ($q) => $q->where('code', 'homs'))
            ->where('is_active', true)
            ->first();

        if (!$branch) {
            $this->command?->error('فرع حمص غير موجود — شغّل GovernorateBranchSeeder أولاً.');

            return;
        }

        $credentials = [];

        foreach (self::HOMS_EMAILS as $email) {
            $user = User::query()->where('email', $email)->first();

            if (!$user) {
                $this->command?->error("الحساب {$email} غير موجود رغم تشغيل NeedsGisAccountsSeeder.");

                continue;
            }

            // تأكيد الربط الصحيح بالنطاق والتفعيل
            $user->forceFill([
                'governorate_id' => $branch->governorate_id,
                'branch_id' => $branch->id,
                'is_active' => true,
            ]);

            // كلمة مرور مؤقتة قوية — تستبدل أي كلمة مرور تجريبية سابقة (12345678)
            $password = self::generateTemporaryPassword();
            $user->password = Hash::make($password);
            $user->save();

            // صلاحيات مباشرة على المستخدم فقط (لا تمس الأدوار العالمية)
            $user->givePermissionTo(self::HOMS_NEEDS_PERMISSIONS);

            if ($user->hasDirectPermission('needs.view_all')) {
                $user->revokePermissionTo('needs.view_all');
            }

            $credentials[$email] = $password;
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        if (!empty($credentials)) {
            $this->command?->warn('بيانات الدخول المؤقتة لحسابات حمص (تُعرض مرة واحدة فقط — احفظها الآن):');
            foreach ($credentials as $email => $password) {
                $this->command?->line("  {$email}  =>  {$password}");
            }
            $this->command?->warn('ملاحظة: النظام لا يدعم حالياً فرض تغيير كلمة المرور عند أول دخول — يُنصح بتغييرها يدوياً من الملف الشخصي فور أول تسجيل دخول.');
        }
    }

    /**
     * كلمة مرور قوية: 16 خانة تتضمن أحرفاً كبيرة وصغيرة وأرقاماً ورموزاً،
     * مع استبعاد المحارف الملتبسة بصرياً.
     */
    public static function generateTemporaryPassword(): string
    {
        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower = 'abcdefghijkmnpqrstuvwxyz';
        $digits = '23456789';
        $symbols = '!@#$%^&*-_+=';

        $chars = [
            $upper[random_int(0, strlen($upper) - 1)],
            $lower[random_int(0, strlen($lower) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
        ];

        $all = $upper . $lower . $digits . $symbols;
        for ($i = count($chars); $i < 16; $i++) {
            $chars[] = $all[random_int(0, strlen($all) - 1)];
        }

        return str_shuffle(implode('', $chars));
    }
}
