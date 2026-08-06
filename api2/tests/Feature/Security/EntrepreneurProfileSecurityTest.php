<?php

namespace Tests\Feature\Security;

use App\Models\EntrepreneurProfile;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EntrepreneurProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function createProfile(User $user, array $overrides = []): EntrepreneurProfile
    {
        $profile = new EntrepreneurProfile(array_merge([
            'full_name' => 'رائد أعمال',
            'project_name' => 'مشروع',
            'status' => 'draft',
        ], $overrides));
        $profile->user_id = $user->id;
        $profile->save();

        return $profile;
    }

    public function test_user_cannot_mass_assign_status_on_update(): void
    {
        $user = User::factory()->create();
        $profile = $this->createProfile($user);
        Sanctum::actingAs($user);

        $this->putJson("/api/entrepreneur/profile/{$profile->id}", [
            'full_name' => 'اسم محدّث',
            'project_name' => 'مشروع محدّث',
            'status' => 'approved',
            'user_id' => 999,
        ])->assertOk();

        $profile->refresh();
        $this->assertSame('draft', $profile->status);
        $this->assertSame($user->id, $profile->user_id);
        $this->assertSame('اسم محدّث', $profile->full_name);
    }

    public function test_user_can_update_allowed_fields_only(): void
    {
        $user = User::factory()->create();
        $profile = $this->createProfile($user);
        Sanctum::actingAs($user);

        $this->putJson("/api/entrepreneur/profile/{$profile->id}", [
            'full_name' => 'اسم جديد',
            'project_name' => 'مشروع جديد',
            'governorate' => 'حلب',
            'executive_summary' => 'ملخص',
        ])->assertOk()
            ->assertJsonPath('full_name', 'اسم جديد')
            ->assertJsonPath('governorate', 'حلب');
    }

    public function test_admin_review_endpoint_still_updates_status(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole(Role::findByName('admin', 'sanctum'));
        $profile = $this->createProfile($user, ['status' => 'submitted']);

        Sanctum::actingAs($admin);

        $this->postJson("/api/entrepreneur/profiles/{$profile->id}/review", [
            'status' => 'approved',
            'reviewer_notes' => 'مقبول',
        ])->assertOk()
            ->assertJsonPath('status', 'approved');

        $profile->refresh();
        $this->assertSame('approved', $profile->status);
        $this->assertSame($admin->id, $profile->reviewed_by);
        $this->assertNotNull($profile->reviewed_at);
    }
}
