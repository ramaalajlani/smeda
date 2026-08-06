<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Support\AccessControlGuard;
use App\Support\TrainingDataScope;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlatformAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    protected function createSuperAdmin(): User
    {
        $user = User::factory()->create([
            'email' => 'superadmin@system.com',
            'is_active' => true,
        ]);
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_platform_admin_roles_are_defined(): void
    {
        $this->assertSame(['general_director', 'admin', 'super_admin'], AccessControlGuard::NATIONAL_ADMIN_ROLES);
    }

    public function test_training_data_scope_does_not_restrict_admin(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();

        $this->assertTrue(TrainingDataScope::hasUnrestrictedTrainingAccess($admin));
        $this->assertTrue(TrainingDataScope::hasBroadTrainingReadAccess($admin));
    }

    public function test_training_data_scope_does_not_restrict_super_admin(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->assertTrue(TrainingDataScope::hasUnrestrictedTrainingAccess($superAdmin));
        $this->assertTrue(TrainingDataScope::hasBroadTrainingReadAccess($superAdmin));
    }

    public function test_admin_can_view_all_training_centers(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $total = TrainingCenter::query()->count();
        $this->assertGreaterThan(0, $total);

        $this->getJson('/api/training-centers?per_page=100')
            ->assertOk()
            ->assertJsonCount($total, 'data');
    }

    public function test_super_admin_can_view_all_training_centers(): void
    {
        Sanctum::actingAs($this->createSuperAdmin());

        $this->getJson('/api/training-centers?per_page=100')->assertOk();
    }

    public function test_admin_can_view_all_trainers(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@system.com')->firstOrFail());

        $this->getJson('/api/trainers?per_page=100')->assertOk();
    }

    public function test_admin_can_view_all_trainees(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@system.com')->firstOrFail());

        $this->getJson('/api/trainees?per_page=100')->assertOk();
    }

    public function test_admin_can_view_all_courses(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@system.com')->firstOrFail());

        $this->getJson('/api/training-courses?per_page=100')->assertOk();
    }

    public function test_admin_can_view_all_registration_requests(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@system.com')->firstOrFail());

        $this->getJson('/api/registration-requests/centers?per_page=100')->assertOk();
        $this->getJson('/api/registration-requests/trainers?per_page=100')->assertOk();
        $this->getJson('/api/registration-requests/trainees?per_page=100')->assertOk();
        $this->getJson('/api/registration-requests/courses?per_page=100')->assertOk();
    }

    public function test_admin_can_view_all_certificates(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@system.com')->firstOrFail());

        $this->getJson('/api/certificates?per_page=100')->assertOk();
    }

    public function test_admin_can_view_all_training_kits_and_programs(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@system.com')->firstOrFail());

        $this->getJson('/api/training-kits?per_page=100')->assertOk();
        $this->getJson('/api/training-programs?per_page=100')->assertOk();
    }

    public function test_admin_can_view_workforces(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@system.com')->firstOrFail());

        $this->getJson('/api/workforces?per_page=100')->assertOk();
    }

    public function test_admin_can_manage_roles_and_permissions_api(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@system.com')->firstOrFail());

        $this->getJson('/api/admin/roles')->assertOk();
        $this->getJson('/api/admin/permissions')->assertOk();
    }

    public function test_admin_can_assign_role_to_user(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'auditor@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users/' . $target->id . '/roles', [
            'role' => 'center_user',
        ])->assertOk();
    }

    public function test_admin_can_revoke_role_from_user(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'auditor@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->deleteJson('/api/admin/users/' . $target->id . '/roles/auditor')
            ->assertOk();
    }

    public function test_admin_can_assign_permission(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'trainee@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users/' . $target->id . '/permissions', [
            'permission' => 'view_reports',
        ])->assertOk();
    }

    public function test_admin_can_revoke_permission(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'trainee@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users/' . $target->id . '/permissions', [
            'permission' => 'view_reports',
        ])->assertOk();

        $this->deleteJson('/api/admin/users/' . $target->id . '/permissions/view_reports')
            ->assertOk();
    }

    public function test_admin_can_access_admin_dashboard_api(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@system.com')->firstOrFail());

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'centers',
                'trainers',
                'trainees',
                'courses',
                'certificates_total',
            ]);

        $this->getJson('/api/admin/access-summary')->assertOk();
    }

    public function test_non_admin_cannot_access_admin_users_api(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'center@system.com')->firstOrFail());

        $this->getJson('/api/admin/users')->assertForbidden();
    }

    public function test_scoped_user_cannot_view_records_outside_scope(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $otherCenterId = TrainingCenter::query()
            ->where('id', '!=', $user->training_center_id)
            ->value('id');

        $this->assertNotNull($otherCenterId);
        $this->getJson('/api/training-centers/' . $otherCenterId)->assertNotFound();
    }

    public function test_policy_before_allows_platform_admin_full_access(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $certificate = Certificate::query()->firstOrFail();

        $this->assertTrue($admin->can('view', $certificate));
        $this->assertTrue($admin->can('issue', $certificate));
        $this->assertTrue($admin->can('print', $certificate));
    }

    public function test_system_admin_has_access_admin_but_not_platform_training_scope(): void
    {
        $user = User::factory()->create(['email' => 'sysadmin@system.com', 'is_active' => true]);
        $user->assignRole('system_admin');

        $this->assertFalse(AccessControlGuard::isPlatformAdministrator($user));
        $this->assertTrue(AccessControlGuard::isAccessAdministrator($user));
        $this->assertFalse(TrainingDataScope::hasUnrestrictedTrainingAccess($user));

        Sanctum::actingAs($user);
        $this->getJson('/api/admin/users')->assertOk();
        $this->getJson('/api/training-centers')->assertForbidden();

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('message', 'استخدم قسم إدارة النظام لإدارة المستخدمين والأدوار والصلاحيات.');
    }
}
