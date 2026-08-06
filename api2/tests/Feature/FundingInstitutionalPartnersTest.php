<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\ConsultantAssignment;
use App\Models\ConsultantOffice;
use App\Models\FundingApplication;
use App\Models\FundingPartner;
use App\Models\FundingPartnerAssignment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FundingInstitutionalPartnersTest extends TestCase
{
    use RefreshDatabase;

    private Branch $aleppo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->aleppo = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();
    }

    private function createUnionAdmin(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('consultant_union_admin', 'sanctum');
        $user->assignRole('consultant_union_admin');

        return $user;
    }

    private function createCentralBankAdmin(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('central_bank_admin', 'sanctum');
        $user->assignRole('central_bank_admin');

        return $user;
    }

    private function createFundingApplication(): FundingApplication
    {
        $owner = User::factory()->create([
            'branch_id' => $this->aleppo->id,
            'governorate_id' => $this->aleppo->governorate_id,
        ]);
        $owner->assignRole('project_owner');

        return FundingApplication::query()->create([
            'application_number' => 'FND-INST-' . uniqid(),
            'applicant_user_id' => $owner->id,
            'applicant_name' => $owner->name,
            'governorate_id' => $this->aleppo->governorate_id,
            'branch_id' => $this->aleppo->id,
            'project_name' => 'مشروع شركاء',
            'requested_amount' => 3000000,
            'status' => 'consultant_review',
            'current_stage' => 'consultant_review',
            'created_by' => $owner->id,
        ]);
    }

    public function test_consultant_union_admin_sees_all_consultant_offices(): void
    {
        ConsultantOffice::query()->create(['name' => 'مكتب 1', 'status' => 'pending', 'created_by' => 1]);
        ConsultantOffice::query()->create(['name' => 'مكتب 2', 'status' => 'active', 'created_by' => 1]);

        Sanctum::actingAs($this->createUnionAdmin());

        $this->getJson('/api/finance/consultant-offices?per_page=100')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_consultant_union_admin_can_approve_consultant_office(): void
    {
        $office = ConsultantOffice::query()->create([
            'name' => 'مكتب جديد',
            'status' => 'pending',
            'created_by' => User::query()->where('email', 'general@system.com')->value('id'),
        ]);

        Sanctum::actingAs($this->createUnionAdmin());

        $this->postJson('/api/finance/consultant-offices/' . $office->id . '/approve')->assertOk();
        $this->assertSame('approved', $office->fresh()->status);
        $this->assertTrue(
            AuditLog::query()->where('action', 'consultant_office_approved')->where('module', 'finance')->exists()
        );
    }

    public function test_unapproved_consultant_office_cannot_receive_assignment(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $app = $this->createFundingApplication();
        $office = ConsultantOffice::query()->create([
            'name' => 'مكتب غير معتمد',
            'status' => 'pending',
            'created_by' => $general->id,
        ]);

        Sanctum::actingAs($general);

        $this->postJson('/api/finance/applications/' . $app->id . '/assign-consultant', [
            'consultant_office_id' => $office->id,
        ])->assertStatus(422);
    }

    public function test_consultant_office_user_sees_only_own_assignments(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $officeA = ConsultantOffice::query()->create(['name' => 'A', 'status' => 'active', 'created_by' => $general->id]);
        $officeB = ConsultantOffice::query()->create(['name' => 'B', 'status' => 'active', 'created_by' => $general->id]);

        $userA = User::factory()->create(['consultant_office_id' => $officeA->id]);
        $userA->assignRole('consultant_office');

        $appA = $this->createFundingApplication();
        $appB = $this->createFundingApplication();

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
    }

    public function test_consultant_submits_price_only_for_own_assignment(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $office = ConsultantOffice::query()->create(['name' => 'مكتب', 'status' => 'active', 'created_by' => $general->id]);
        $consultant = User::factory()->create(['consultant_office_id' => $office->id]);
        $consultant->assignRole('consultant_office');

        $app = $this->createFundingApplication();
        $assignment = ConsultantAssignment::query()->create([
            'funding_application_id' => $app->id,
            'consultant_office_id' => $office->id,
            'assigned_by' => $general->id,
            'assigned_at' => now(),
            'status' => 'accepted',
        ]);

        Sanctum::actingAs($consultant);

        $this->postJson('/api/finance/consultant-assignments/' . $assignment->id . '/price-offer', [
            'price_offer_amount' => 500000,
        ])->assertOk();

        $this->assertTrue(
            AuditLog::query()->where('action', 'consultant_price_submitted')->where('module', 'finance')->exists()
        );
    }

    public function test_central_bank_admin_sees_all_partners(): void
    {
        FundingPartner::query()->create(['name' => 'بنك 1', 'status' => 'pending', 'created_by' => 1, 'partner_type' => 'bank']);
        FundingPartner::query()->create(['name' => 'بنك 2', 'status' => 'active', 'created_by' => 1, 'partner_type' => 'bank']);

        Sanctum::actingAs($this->createCentralBankAdmin());

        $this->getJson('/api/finance/partners?per_page=100')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_central_bank_admin_can_approve_partner(): void
    {
        $partner = FundingPartner::query()->create([
            'name' => 'مصرف جديد',
            'status' => 'pending',
            'partner_type' => 'bank',
            'created_by' => User::query()->where('email', 'general@system.com')->value('id'),
        ]);

        Sanctum::actingAs($this->createCentralBankAdmin());

        $this->postJson('/api/finance/partners/' . $partner->id . '/approve')->assertOk();
        $this->assertSame('approved', $partner->fresh()->status);
        $this->assertTrue(
            AuditLog::query()->where('action', 'funding_partner_approved')->where('module', 'finance')->exists()
        );
    }

    public function test_unapproved_partner_cannot_receive_assignment(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $app = $this->createFundingApplication();
        $partner = FundingPartner::query()->create([
            'name' => 'بنك غير معتمد',
            'status' => 'pending',
            'partner_type' => 'bank',
            'created_by' => $general->id,
        ]);

        Sanctum::actingAs($general);

        $this->postJson('/api/finance/applications/' . $app->id . '/assign-partner', [
            'funding_partner_id' => $partner->id,
        ])->assertStatus(422);
    }

    public function test_funding_partner_sees_only_own_assignments(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $bankA = FundingPartner::query()->create(['name' => 'A', 'status' => 'active', 'partner_type' => 'bank', 'created_by' => $general->id]);
        $bankB = FundingPartner::query()->create(['name' => 'B', 'status' => 'active', 'partner_type' => 'bank', 'created_by' => $general->id]);

        $userA = User::factory()->create(['funding_partner_id' => $bankA->id]);
        $userA->assignRole('funding_partner');

        $appA = $this->createFundingApplication();
        $appB = $this->createFundingApplication();

        FundingPartnerAssignment::query()->create([
            'funding_application_id' => $appA->id,
            'funding_partner_id' => $bankA->id,
            'assigned_by' => $general->id,
            'assigned_at' => now(),
            'status' => 'sent',
        ]);
        FundingPartnerAssignment::query()->create([
            'funding_application_id' => $appB->id,
            'funding_partner_id' => $bankB->id,
            'assigned_by' => $general->id,
            'assigned_at' => now(),
            'status' => 'sent',
        ]);

        Sanctum::actingAs($userA);

        $this->getJson('/api/finance/applications/' . $appA->id)->assertOk();
        $this->getJson('/api/finance/applications/' . $appB->id)->assertForbidden();
    }

    public function test_partner_decision_only_for_own_assignment(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $bank = FundingPartner::query()->create(['name' => 'مصرف', 'status' => 'active', 'partner_type' => 'bank', 'created_by' => $general->id]);
        $partnerUser = User::factory()->create(['funding_partner_id' => $bank->id]);
        $partnerUser->assignRole('funding_partner');

        $app = $this->createFundingApplication();
        $assignment = FundingPartnerAssignment::query()->create([
            'funding_application_id' => $app->id,
            'funding_partner_id' => $bank->id,
            'assigned_by' => $general->id,
            'assigned_at' => now(),
            'status' => 'sent',
        ]);

        Sanctum::actingAs($partnerUser);

        $this->postJson('/api/finance/partner-assignments/' . $assignment->id . '/decision', [
            'decision' => 'approved',
            'approved_amount' => 2000000,
        ])->assertOk();

        $this->assertTrue(
            AuditLog::query()->where('action', 'funding_partner_decision_submitted')->where('module', 'finance')->exists()
        );
    }

    public function test_branch_manager_cannot_manage_offices_or_partners(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $this->postJson('/api/finance/consultant-offices', ['name' => 'مكتب'])->assertForbidden();
        $this->postJson('/api/finance/partners', ['name' => 'بنك'])->assertForbidden();
    }

    public function test_general_director_supervises_all_institutional_endpoints(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->getJson('/api/finance/consultant-union/dashboard')->assertOk();
        $this->getJson('/api/finance/central-bank/dashboard')->assertOk();
    }

    public function test_auditor_is_read_only_on_institutional_mutations(): void
    {
        $office = ConsultantOffice::query()->create(['name' => 'مكتب', 'status' => 'pending', 'created_by' => 1]);

        Sanctum::actingAs(User::query()->where('email', 'auditor@system.com')->firstOrFail());

        $this->getJson('/api/finance/consultant-offices')->assertOk();
        $this->postJson('/api/finance/consultant-offices/' . $office->id . '/approve')->assertForbidden();
    }

    public function test_idor_on_partner_show_returns_forbidden(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $bank = FundingPartner::query()->create(['name' => 'مصرف', 'status' => 'active', 'partner_type' => 'bank', 'created_by' => $general->id]);
        $otherBank = FundingPartner::query()->create(['name' => 'آخر', 'status' => 'active', 'partner_type' => 'bank', 'created_by' => $general->id]);

        $partnerUser = User::factory()->create(['funding_partner_id' => $bank->id]);
        $partnerUser->assignRole('funding_partner');

        Sanctum::actingAs($partnerUser);

        $this->getJson('/api/finance/partners/' . $otherBank->id)->assertForbidden();
    }
}
