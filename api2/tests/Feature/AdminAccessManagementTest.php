<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAccessManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_regular_user_cannot_list_roles(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $this->getJson('/api/admin/roles')->assertForbidden();
    }

    public function test_admin_can_list_roles(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/roles')->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'is_protected']]]);
    }

    public function test_admin_can_assign_role_to_user(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'auditor@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users/' . $target->id . '/roles', [
            'role' => 'center_user',
        ])->assertOk();

        $this->assertTrue($target->fresh()->hasRole('center_user'));
    }

    public function test_admin_can_revoke_role_from_user(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'auditor@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->deleteJson('/api/admin/users/' . $target->id . '/roles/auditor')
            ->assertOk();

        $this->assertFalse($target->fresh()->hasRole('auditor'));
    }

    public function test_admin_cannot_revoke_own_admin_role_when_last_admin(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->deleteJson('/api/admin/users/' . $admin->id . '/roles/admin')
            ->assertForbidden();
    }

    public function test_cannot_delete_admin_role(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

        $this->deleteJson('/api/admin/roles/' . $adminRole->id)
            ->assertUnprocessable();
    }

    public function test_cannot_assign_nonexistent_role(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'auditor@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users/' . $target->id . '/roles', [
            'role' => 'nonexistent_role_xyz',
        ])->assertUnprocessable();
    }

    public function test_cannot_assign_nonexistent_permission(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'auditor@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users/' . $target->id . '/permissions', [
            'permission' => 'nonexistent_permission_xyz',
        ])->assertUnprocessable();
    }

    public function test_direct_permission_works(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'trainee@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users/' . $target->id . '/permissions', [
            'permission' => 'view_reports',
        ])->assertOk();

        $this->assertTrue($target->fresh()->hasDirectPermission('view_reports'));
    }

    public function test_role_permission_sync_works(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $role = Role::create(['name' => 'test_custom_role', 'guard_name' => 'sanctum']);

        $this->postJson('/api/admin/roles/' . $role->id . '/permissions', [
            'permissions' => ['view_reports', 'view_audit'],
        ])->assertOk();

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('view_reports'));
        $this->assertTrue($role->hasPermissionTo('view_audit'));
    }

    public function test_assign_role_creates_audit_log(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'trainee@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $before = AuditLog::query()->where('action', 'user_role_assigned')->count();

        $this->postJson('/api/admin/users/' . $target->id . '/roles', [
            'role' => 'auditor',
        ])->assertOk();

        $this->assertSame($before + 1, AuditLog::query()->where('action', 'user_role_assigned')->count());
    }

    public function test_register_rejects_admin_role(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'account_type' => 'trainee',
            'role' => 'admin',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_permissions_index_returns_grouped_modules(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/permissions');
        $response->assertOk();
        $response->assertJsonStructure(['data' => ['system', 'certificates']]);
    }

    public function test_access_summary_for_admin(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/access-summary')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'users_count', 'roles_count', 'permissions_count',
                    'admin_users_count', 'recent_access_logs', 'roles', 'permissions',
                ],
            ]);
    }

    public function test_admin_can_deactivate_user(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'auditor@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->patchJson('/api/admin/users/' . $target->id . '/status', [
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_admin_can_activate_user(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'auditor@system.com')->firstOrFail();
        $target->update(['is_active' => false]);
        Sanctum::actingAs($admin);

        $this->patchJson('/api/admin/users/' . $target->id . '/status', [
            'is_active' => true,
        ])->assertOk()
            ->assertJsonPath('data.is_active', true);

        $this->assertTrue($target->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->patchJson('/api/admin/users/' . $admin->id . '/status', [
            'is_active' => false,
        ])->assertForbidden();
    }

    public function test_admin_cannot_deactivate_last_active_admin(): void
    {
        User::query()->where('email', 'general@system.com')->update(['is_active' => false]);

        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'auditor@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users/' . $target->id . '/roles', [
            'role' => 'admin',
        ])->assertOk();

        $target->update(['is_active' => false]);

        Sanctum::actingAs($target);

        $this->patchJson('/api/admin/users/' . $admin->id . '/status', [
            'is_active' => false,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['is_active']);

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_deactivate_user_creates_audit_log(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'trainee@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $before = AuditLog::query()->where('action', 'user_deactivated')->count();

        $this->patchJson('/api/admin/users/' . $target->id . '/status', [
            'is_active' => false,
        ])->assertOk();

        $this->assertSame($before + 1, AuditLog::query()->where('action', 'user_deactivated')->count());
    }
}
