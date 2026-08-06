<?php

namespace Database\Seeders;

use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * ينشئ حساب اختبار لمركز تدريبي (center_user) ويربطه بالمركز الذي يملك أكبر عدد
 * دورات، حتى تظهر بيانات فعلية في تطبيق المركز التدريبي عند تسجيل الدخول.
 *
 * التشغيل:  php artisan db:seed --class=TrainingCenterTestUserSeeder
 */
class TrainingCenterTestUserSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // اختر المركز الأكثر دورات (لتظهر بيانات)، وإلا أول مركز موجود.
        $centerId = TrainingCourse::query()
            ->select('training_center_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('training_center_id')
            ->groupBy('training_center_id')
            ->orderByDesc('total')
            ->value('training_center_id');

        $center = $centerId
            ? TrainingCenter::find($centerId)
            : TrainingCenter::query()->first();

        if (!$center) {
            $this->command?->error('لا يوجد أي مركز تدريبي في قاعدة البيانات — استورد بيانات المراكز أولاً.');

            return;
        }

        $coursesCount = TrainingCourse::where('training_center_id', $center->id)->count();

        $user = User::updateOrCreate(
            ['email' => 'center@smedc.gov.sy'],
            [
                'name' => 'مركز تدريبي (اختبار)',
                'password' => bcrypt('12345678'),
                'entity_type' => 'center_user',
                'training_center_id' => $center->id,
                'trainer_id' => null,
                'trainee_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles(['center_user']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info('✅ تم إنشاء/تحديث حساب المركز التدريبي:');
        $this->command?->line('   البريد    : center@smedc.gov.sy');
        $this->command?->line('   كلمة السر : 12345678');
        $this->command?->line('   الدور     : center_user');
        $this->command?->line("   المركز    : {$center->name} (#{$center->id})");
        $this->command?->line("   عدد دوراته: {$coursesCount}");
    }
}
