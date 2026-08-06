<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ConsultantAssignment;
use App\Models\ConsultantOffice;
use App\Models\FundingApplication;
use App\Models\FundingPartner;
use App\Models\FundingPartnerAssignment;
use App\Models\Need;
use App\Models\User;
use App\Support\DashboardAccess;
use App\Support\NeedStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAccessIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Branch $aleppo;
    private Branch $tartus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->aleppo = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();
        $this->tartus = Branch::query()->where('code', 'BR-TARTUS')->firstOrFail();
    }

    private function userWithRole(string $role, Branch $branch, array $extra = []): User
    {
        $user = User::factory()->create(array_merge([
            'governorate_id' => $branch->governorate_id,
            'branch_id' => $branch->id,
        ], $extra));
        $user->assignRole(Role::findByName($role, 'sanctum'));

        return $user;
    }

    private function createFundingApplication(User $creator, Branch $branch, array $overrides = []): FundingApplication
    {
        return FundingApplication::query()->create(array_merge([
            'application_number' => 'FND-' . uniqid(),
            'applicant_name' => 'مقدم طلب اختبار',
            'applicant_user_id' => $creator->id,
            'governorate_id' => $branch->governorate_id,
            'branch_id' => $branch->id,
            'project_name' => 'مشروع اختبار',
            'requested_amount' => 1000000,
            'status' => 'consultant_review',
            'current_stage' => 'consultant_review',
            'created_by' => $creator->id,
        ], $overrides));
    }

    private function createNeed(Branch $branch, User $creator, array $overrides = []): Need
    {
        return Need::query()->create(array_merge([
            'need_code' => 'NEED-ISO-' . uniqid(),
            'title' => 'احتياج عزل',
            'need_owner_type' => 'citizen',
            'need_scope' => 'individual',
            'source_platform' => 'gis',
            'governorate_id' => $branch->governorate_id,
            'branch_id' => $branch->id,
            'status' => NeedStatus::PENDING_GOVERNORATE_REVIEW,
            'approval_status' => NeedStatus::PENDING_GOVERNORATE_REVIEW,
            'priority' => 'متوسطة',
            'created_by' => $creator->id,
            'updated_by' => $creator->id,
        ], $overrides));
    }

    public function test_normal_user_cannot_access_admin_dashboard(): void
    {
        $plainUser = User::factory()->create();
        Sanctum::actingAs($plainUser);

        $this->getJson('/api/admin/access-summary')->assertForbidden();
        $this->getJson('/api/dashboard')->assertForbidden();
        $this->assertFalse(DashboardAccess::canAccessMainDashboard($plainUser));
    }

    public function test_project_owner_sees_only_own_dashboard_data(): void
    {
        $owner = $this->userWithRole('project_owner', $this->aleppo);
        $other = $this->userWithRole('project_owner', $this->tartus);

        $this->createFundingApplication($owner, $this->aleppo, [
            'application_number' => 'FND-OWN-' . uniqid(),
            'applicant_user_id' => $owner->id,
        ]);
        $this->createFundingApplication($other, $this->tartus, [
            'application_number' => 'FND-OTH-' . uniqid(),
            'applicant_user_id' => $other->id,
        ]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/dashboard')->assertOk()
            ->assertJsonPath('dashboard_role', 'project_owner')
            ->assertJsonPath('scope', 'entrepreneur')
            ->assertJsonPath('funding_applications_total', 1)
            ->assertJsonMissingPath('users_total');
    }

    public function test_branch_manager_cannot_access_other_governorate_data(): void
    {
        $aleppoManager = User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail();
        $need = $this->createNeed($this->tartus, User::factory()->create([
            'branch_id' => $this->tartus->id,
            'governorate_id' => $this->tartus->governorate_id,
        ]));

        Sanctum::actingAs($aleppoManager);

        $this->getJson('/api/needs/' . $need->id)->assertForbidden();
    }

    public function test_governor_cannot_access_other_governorate_needs(): void
    {
        $governor = User::query()->where('email', 'governor.tartus@system.com')->firstOrFail();
        $need = $this->createNeed($this->aleppo, User::factory()->create([
            'branch_id' => $this->aleppo->id,
            'governorate_id' => $this->aleppo->governorate_id,
        ]));

        Sanctum::actingAs($governor);

        $this->getJson('/api/needs/' . $need->id)->assertForbidden();
    }

    public function test_consultant_office_cannot_view_other_office_requests(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $officeA = ConsultantOffice::query()->create(['name' => 'مكتب أ', 'status' => 'active', 'created_by' => $general->id]);
        $officeB = ConsultantOffice::query()->create(['name' => 'مكتب ب', 'status' => 'active', 'created_by' => $general->id]);

        $userA = User::factory()->create(['consultant_office_id' => $officeA->id]);
        $userA->assignRole(Role::findByName('consultant_office', 'sanctum'));

        $appA = $this->createFundingApplication($general, $this->aleppo, [
            'application_number' => 'FND-A-' . uniqid(),
            'project_name' => 'مشروع أ',
            'status' => 'consultant_review',
            'current_stage' => 'consultant_review',
        ]);
        $appB = $this->createFundingApplication($general, $this->aleppo, [
            'application_number' => 'FND-B-' . uniqid(),
            'project_name' => 'مشروع ب',
            'status' => 'consultant_review',
            'current_stage' => 'consultant_review',
        ]);

        ConsultantAssignment::query()->create([
            'funding_application_id' => $appA->id,
            'consultant_office_id' => $officeA->id,
            'assigned_by' => $general->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);
        ConsultantAssignment::query()->create([
            'funding_application_id' => $appB->id,
            'consultant_office_id' => $officeB->id,
            'assigned_by' => $general->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        Sanctum::actingAs($userA);

        $this->getJson('/api/finance/applications/' . $appA->id)->assertOk();
        $this->getJson('/api/finance/applications/' . $appB->id)->assertForbidden();
        $this->getJson('/api/finance/consultant-office/dashboard')->assertOk();
    }

    public function test_funding_partner_cannot_view_other_partner_records(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $partnerA = FundingPartner::query()->create(['name' => 'بنك أ', 'status' => 'active', 'partner_type' => 'bank', 'created_by' => $general->id]);
        $partnerB = FundingPartner::query()->create(['name' => 'بنك ب', 'status' => 'active', 'partner_type' => 'bank', 'created_by' => $general->id]);

        $userA = User::factory()->create(['funding_partner_id' => $partnerA->id]);
        $userA->assignRole(Role::findByName('funding_partner', 'sanctum'));

        $app = $this->createFundingApplication($general, $this->aleppo, [
            'application_number' => 'FND-P-' . uniqid(),
            'project_name' => 'مشروع تمويل',
            'requested_amount' => 3000000,
            'status' => 'funder_review',
            'current_stage' => 'funder_review',
        ]);

        FundingPartnerAssignment::query()->create([
            'funding_application_id' => $app->id,
            'funding_partner_id' => $partnerB->id,
            'assigned_by' => $general->id,
            'assigned_at' => now(),
            'status' => 'sent',
        ]);

        Sanctum::actingAs($userA);

        $this->getJson('/api/finance/applications/' . $app->id)->assertForbidden();
        $this->getJson('/api/finance/funding-partner/dashboard')->assertOk()
            ->assertJsonPath('data.assignments_total', 0);
    }

    public function test_data_entry_can_create_need_but_cannot_approve(): void
    {
        $entry = $this->userWithRole('data_entry', $this->aleppo);
        $need = $this->createNeed($this->aleppo, $entry);

        Sanctum::actingAs($entry);

        $this->postJson('/api/needs', [
            'title' => 'احتياج جديد',
            'need_owner_type' => 'citizen',
            'governorate_id' => $this->aleppo->governorate_id,
            'branch_id' => $this->aleppo->id,
            'priority' => 'متوسطة',
            'sector' => 'زراعة',
            'latitude' => 36.202000,
            'longitude' => 37.134000,
        ])->assertCreated();

        $this->postJson('/api/needs/' . $need->id . '/approve', ['note' => 'test'])->assertForbidden();
        $this->getJson('/api/needs/workspace/data-entry')->assertOk();
        $this->getJson('/api/needs/dashboard')->assertForbidden();
    }

    public function test_data_reviewer_can_review_only_in_scope(): void
    {
        $reviewer = $this->userWithRole('data_reviewer', $this->aleppo);
        $ownNeed = $this->createNeed($this->aleppo, User::factory()->create([
            'branch_id' => $this->aleppo->id,
            'governorate_id' => $this->aleppo->governorate_id,
        ]));
        $otherNeed = $this->createNeed($this->tartus, User::factory()->create([
            'branch_id' => $this->tartus->id,
            'governorate_id' => $this->tartus->governorate_id,
        ]));

        Sanctum::actingAs($reviewer);

        $this->getJson('/api/needs/workspace/reviewer')->assertOk();
        $this->postJson('/api/needs/' . $ownNeed->id . '/review', ['note' => 'مراجعة'])->assertOk();
        $this->postJson('/api/needs/' . $otherNeed->id . '/review', ['note' => 'مراجعة'])->assertForbidden();
    }

    public function test_admin_can_access_all_dashboards(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->getJson('/api/dashboard')->assertOk();
        $this->getJson('/api/admin/access-summary')->assertOk();
        $this->getJson('/api/needs/dashboard')->assertOk();
        $this->getJson('/api/finance/manager/dashboard')->assertOk();
    }

    public function test_unknown_role_cannot_access_main_dashboard_api(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('custom_unknown_role', 'sanctum');
        $user->assignRole('custom_unknown_role');

        $this->assertFalse(DashboardAccess::canAccessMainDashboard($user));

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')->assertForbidden();
    }

    public function test_dashboard_api_returns_scoped_data_by_role(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $partner = FundingPartner::query()->create(['name' => 'بنك الاختبار', 'status' => 'active', 'partner_type' => 'bank', 'created_by' => $general->id]);
        $partnerUser = User::factory()->create(['funding_partner_id' => $partner->id]);
        $partnerUser->assignRole(Role::findByName('funding_partner', 'sanctum'));

        $app = $this->createFundingApplication($general, $this->aleppo, [
            'application_number' => 'FND-SCOPE-' . uniqid(),
            'status' => 'funder_review',
            'current_stage' => 'funder_review',
        ]);

        FundingPartnerAssignment::query()->create([
            'funding_application_id' => $app->id,
            'funding_partner_id' => $partner->id,
            'assigned_by' => $general->id,
            'assigned_at' => now(),
            'status' => 'under_review',
        ]);

        Sanctum::actingAs($partnerUser);

        $this->getJson('/api/dashboard')->assertOk()
            ->assertJsonPath('scope', 'funding_partner')
            ->assertJsonPath('assignments_total', 1)
            ->assertJsonPath('under_review', 1)
            ->assertJsonMissingPath('users_total');
    }

    public function test_normal_user_cannot_access_finance_applications_or_admin_workforce_routes(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/finance/applications')->assertForbidden();
        $this->getJson('/api/workforces')->assertForbidden();
        $this->getJson('/api/workforce/job-applications')->assertForbidden();
        $this->getJson('/api/consulting/requests')->assertForbidden();
    }

    public function test_dashboard_endpoints_reject_wrong_roles(): void
    {
        $entry = $this->userWithRole('data_entry', $this->aleppo);
        Sanctum::actingAs($entry);

        $this->getJson('/api/finance/funding-partner/dashboard')->assertForbidden();
        $this->getJson('/api/finance/consultant-office/dashboard')->assertForbidden();
        $this->getJson('/api/finance/manager/dashboard')->assertForbidden();
        $this->getJson('/api/needs/workspace/reviewer')->assertForbidden();

        $owner = $this->userWithRole('project_owner', $this->aleppo);
        Sanctum::actingAs($owner);

        $this->getJson('/api/needs/workspace/data-entry')->assertForbidden();
        $this->getJson('/api/finance/consultant-office/dashboard')->assertForbidden();
        $this->getJson('/api/finance/manager/dashboard')->assertForbidden();
    }

    public function test_auditor_dashboard_is_read_only(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'auditor@system.com')->firstOrFail());

        $this->getJson('/api/dashboard')->assertOk()
            ->assertJsonPath('dashboard_role', 'auditor')
            ->assertJsonPath('scope', 'audit_readonly');

        $this->postJson('/api/finance/records', [
            'record_type' => 'funding',
            'title' => 'Denied',
            'amount' => 1,
        ])->assertForbidden();
    }

    public function test_governor_dashboard_returns_governorate_scope(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'governor.tartus@system.com')->firstOrFail());

        $this->getJson('/api/dashboard')->assertOk()
            ->assertJsonPath('dashboard_role', 'governor')
            ->assertJsonPath('scope', 'governorate')
            ->assertJsonStructure(['needs_total', 'operational_links']);
    }

    public function test_branch_manager_dashboard_excludes_national_users_total(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $this->getJson('/api/dashboard')->assertOk()
            ->assertJsonPath('dashboard_role', 'branch_manager')
            ->assertJsonMissingPath('users_total');
    }

    public function test_branch_officer_can_access_own_dashboard(): void
    {
        $officer = $this->userWithRole('branch_officer', $this->aleppo);

        Sanctum::actingAs($officer);

        $this->getJson('/api/dashboard')->assertOk()
            ->assertJsonPath('dashboard_role', 'branch_officer')
            ->assertJsonPath('scope', 'branch_officer')
            ->assertJsonMissingPath('users_total');
    }

    public function test_branch_officer_cannot_access_admin_dashboard(): void
    {
        Sanctum::actingAs($this->userWithRole('branch_officer', $this->aleppo));

        $this->getJson('/api/admin/access-summary')->assertForbidden();
        $this->getJson('/api/admin/users')->assertForbidden();
    }

    public function test_branch_officer_cannot_access_other_branch_data(): void
    {
        $officer = $this->userWithRole('branch_officer', $this->aleppo);
        $need = $this->createNeed($this->tartus, User::factory()->create([
            'branch_id' => $this->tartus->id,
            'governorate_id' => $this->tartus->governorate_id,
        ]));

        Sanctum::actingAs($officer);

        $this->getJson('/api/needs/' . $need->id)->assertForbidden();
    }

    public function test_workforce_manager_can_access_workforce_dashboard(): void
    {
        Sanctum::actingAs($this->userWithRole('workforce_manager', $this->aleppo));

        $this->getJson('/api/dashboard')->assertOk()
            ->assertJsonPath('dashboard_role', 'workforce_manager')
            ->assertJsonPath('scope', 'workforce')
            ->assertJsonStructure(['jobs_published', 'operational_links']);

        $this->getJson('/api/workforces')->assertOk();
    }

    public function test_workforce_manager_cannot_access_admin_dashboard(): void
    {
        Sanctum::actingAs($this->userWithRole('workforce_manager', $this->aleppo));

        $this->getJson('/api/admin/access-summary')->assertForbidden();
    }

    public function test_normal_user_cannot_access_workforce_management(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')->assertForbidden();
        $this->getJson('/api/workforces')->assertForbidden();
        $this->postJson('/api/workforce/job-postings', ['title' => 'x'])->assertForbidden();
    }
}
