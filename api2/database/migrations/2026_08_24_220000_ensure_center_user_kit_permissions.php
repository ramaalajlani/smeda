<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * إصلاح 403 على GET /training-kits لحسابات center_user
 * عندما تكون صلاحيات الدور غير متزامنة على الإنتاج.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $centerTrainingPermissions = [
        'view_centers',
        'manage_centers',
        'view_kits',
        'manage_kits',
        'view_trainers',
        'manage_trainers',
        'view_courses',
        'manage_courses',
        'view_course_details',
        'view_trainees',
        'manage_trainees',
        'view_certificates',
        'issue_certificates',
        'view_certificate_approvals',
        'approve_center_certificates',
        'print_certificates',
        'verify_certificates',
        'view_registration_requests',
        'create_center_registration_requests',
        'create_trainer_registration_requests',
        'create_trainee_registration_requests',
        'complete_course_registration_requests',
    ];

    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'sanctum';

        foreach ($this->centerTrainingPermissions as $name) {
            Permission::findOrCreate($name, $guard);
        }

        $role = Role::query()->where('name', 'center_user')->where('guard_name', $guard)->first();
        if (!$role) {
            return;
        }

        $role->givePermissionTo($this->centerTrainingPermissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // لا نزيل الصلاحيات عمداً — قد تكون مطلوبة لحسابات قائمة.
    }
};
