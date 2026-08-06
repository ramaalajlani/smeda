<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Support\DashboardAccess;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardAccessRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Role::findOrCreate('incubator_manager', 'sanctum');
        Role::findOrCreate('media_manager', 'sanctum');
    }

    public function test_incubator_manager_can_access_main_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('incubator_manager');

        $this->assertTrue(DashboardAccess::canAccessMainDashboard($user));

        Sanctum::actingAs($user);
        $this->getJson('/api/dashboard')->assertOk();
    }

    public function test_media_manager_can_access_main_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('media_manager');

        $this->assertTrue(DashboardAccess::canAccessMainDashboard($user));

        Sanctum::actingAs($user);
        $this->getJson('/api/dashboard')->assertOk();
    }

    public function test_plain_user_without_role_cannot_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(DashboardAccess::canAccessMainDashboard($user));

        Sanctum::actingAs($user);
        $this->getJson('/api/dashboard')->assertForbidden();
    }
}
