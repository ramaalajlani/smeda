<?php

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\ConsultingRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ConsultingCrossBranchMutationTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branchA;
    private Branch $branchB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->branchA = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();
        $this->branchB = Branch::query()->create([
            'code' => 'BR-ALEPPO-CON-TEST',
            'governorate_id' => $this->branchA->governorate_id,
            'name' => 'فرع حلب استشارات — اختبار',
            'is_active' => true,
        ]);
    }

    private function branchManager(Branch $branch): User
    {
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'governorate_id' => $branch->governorate_id,
        ]);
        $user->assignRole(Role::findByName('branch_manager', 'sanctum'));

        return $user;
    }

    private function consultingRequest(Branch $branch): ConsultingRequest
    {
        $owner = User::factory()->create([
            'branch_id' => $branch->id,
            'governorate_id' => $branch->governorate_id,
        ]);
        $owner->assignRole(Role::findByName('project_owner', 'sanctum'));

        return ConsultingRequest::query()->create([
            'user_id' => $owner->id,
            'branch_id' => $branch->id,
            'governorate_id' => $branch->governorate_id,
            'category_code' => 'CON-01',
            'request_type' => 'new_project',
            'title' => 'طلب استشارة عزل',
            'description' => 'وصف اختبار',
            'status' => 'submitted',
        ]);
    }

    public function test_branch_manager_cannot_sort_request_from_sibling_branch_in_same_governorate(): void
    {
        $managerA = $this->branchManager($this->branchA);
        $requestB = $this->consultingRequest($this->branchB);

        Sanctum::actingAs($managerA);

        $this->postJson('/api/consulting/requests/' . $requestB->id . '/sort', [
            'action' => 'approve',
        ])->assertForbidden();
    }

    public function test_branch_manager_cannot_transfer_request_from_sibling_branch_in_same_governorate(): void
    {
        $managerA = $this->branchManager($this->branchA);
        $requestB = $this->consultingRequest($this->branchB);

        Sanctum::actingAs($managerA);

        $this->postJson('/api/consulting/requests/' . $requestB->id . '/transfer', [
            'target' => 'financing',
        ])->assertForbidden();
    }

    public function test_branch_manager_can_sort_request_in_own_branch(): void
    {
        $managerA = $this->branchManager($this->branchA);
        $requestA = $this->consultingRequest($this->branchA);

        Sanctum::actingAs($managerA);

        $this->postJson('/api/consulting/requests/' . $requestA->id . '/sort', [
            'action' => 'approve',
        ])->assertOk()
            ->assertJsonPath('data.status', 'awaiting_offers');
    }
}
