<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NeedsRouteMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Role::findOrCreate('data_entry', 'sanctum');
    }

    public function test_user_without_needs_permission_cannot_list_needs(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/needs')->assertForbidden();
    }

    public function test_data_entry_user_can_list_needs(): void
    {
        $user = User::factory()->create();
        $user->assignRole('data_entry');
        Sanctum::actingAs($user);

        $this->getJson('/api/needs')->assertOk();
    }
}
