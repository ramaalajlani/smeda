<?php

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\FundingApplication;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinanceBranchOfficerIsolationTest extends TestCase
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
            'code' => 'BR-ALEPPO-SIB-TEST',
            'governorate_id' => $this->branchA->governorate_id,
            'name' => 'فرع حلب ثانٍ — اختبار عزل',
            'is_active' => true,
        ]);
    }

    private function branchOfficer(Branch $branch): User
    {
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'governorate_id' => $branch->governorate_id,
        ]);
        $user->assignRole(Role::findByName('branch_officer', 'sanctum'));

        return $user;
    }

    private function createFundingApplicationForBranch(Branch $branch): FundingApplication
    {
        $owner = User::factory()->create([
            'branch_id' => $branch->id,
            'governorate_id' => $branch->governorate_id,
        ]);
        $owner->assignRole(Role::findByName('project_owner', 'sanctum'));

        return FundingApplication::query()->create([
            'application_number' => 'FND-ISO-' . uniqid(),
            'applicant_user_id' => $owner->id,
            'applicant_name' => 'مقدم طلب فرع آخر',
            'phone' => '0911111111',
            'email' => 'other-branch@example.com',
            'governorate_id' => $branch->governorate_id,
            'branch_id' => $branch->id,
            'project_name' => 'مشروع فرع آخر',
            'requested_amount' => 2500000,
            'status' => 'submitted',
            'current_stage' => 'submitted',
            'created_by' => $owner->id,
        ]);
    }

    public function test_branch_officer_in_branch_a_cannot_see_branch_b_applications_in_same_governorate(): void
    {
        $officerA = $this->branchOfficer($this->branchA);
        $appB = $this->createFundingApplicationForBranch($this->branchB);

        Sanctum::actingAs($officerA);

        $response = $this->getJson('/api/finance/applications?per_page=100')->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertNotContains((int) $appB->id, $ids);
        $this->getJson('/api/finance/applications/' . $appB->id)->assertForbidden();
    }
}
