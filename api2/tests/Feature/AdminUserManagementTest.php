<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_create_branch_manager_with_branch_scope(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $branch = \App\Models\Branch::query()->where('code', 'BR-HOMS')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users', [
            'name' => 'Homs Branch Manager',
            'email' => 'branch.homs@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'role' => 'branch_manager',
            'governorate_id' => $branch->governorate_id,
            'branch_id' => $branch->id,
            'is_active' => true,
        ])->assertCreated()
            ->assertJsonPath('data.branch_id', $branch->id)
            ->assertJsonPath('data.governorate_id', $branch->governorate_id);

        $this->assertDatabaseHas('users', [
            'email' => 'branch.homs@example.com',
            'branch_id' => $branch->id,
            'governorate_id' => $branch->governorate_id,
        ]);
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users', [
            'name' => 'New Auditor',
            'email' => 'newauditor@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'role' => 'auditor',
            'is_active' => true,
        ])->assertCreated()
            ->assertJsonPath('data.email', 'newauditor@example.com');

        $this->assertDatabaseHas('users', ['email' => 'newauditor@example.com']);
        $this->assertTrue(AuditLog::query()->where('action', 'user_created')->exists());
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'auditor@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->putJson('/api/admin/users/' . $target->id, [
            'name' => 'Updated Auditor Name',
            'phone' => '0999999999',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated Auditor Name');
    }

    public function test_admin_can_change_user_password(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'trainee@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users/' . $target->id . '/change-password', [
            'password' => 'newpass99',
            'password_confirmation' => 'newpass99',
        ])->assertOk();

        $this->assertTrue(AuditLog::query()->where('action', 'user_password_changed')->exists());
    }

    public function test_admin_can_sync_user_roles(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'auditor@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users/' . $target->id . '/roles/sync', [
            'roles' => ['auditor', 'center_user'],
        ])->assertOk();

        $this->assertTrue($target->fresh()->hasRole('center_user'));
    }

    public function test_admin_can_sync_user_permissions(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'trainee@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users/' . $target->id . '/permissions/sync', [
            'permissions' => ['view_reports'],
        ])->assertOk();

        $this->assertTrue($target->fresh()->hasDirectPermission('view_reports'));
    }

    public function test_admin_can_list_activity_logs(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        AuditLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'user_created',
            'module' => 'users',
            'description' => 'test',
            'created_at' => now(),
        ]);

        $this->getJson('/api/admin/activity-logs')->assertOk()
            ->assertJsonStructure(['data' => [['id', 'action', 'module']]]);
    }

    public function test_center_user_cannot_create_admin_user(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'center@system.com')->firstOrFail());

        $this->postJson('/api/admin/users', [
            'name' => 'Hack',
            'email' => 'hack@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'role' => 'admin',
        ])->assertForbidden();
    }

    public function test_admin_dashboard_includes_user_stats(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@system.com')->firstOrFail());

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'users_total',
                'users_active',
                'users_inactive',
                'recent_activity',
            ]);
    }
}
