<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\ConsultantAssignment;
use App\Models\ConsultantOffice;
use App\Models\ConsultantReport;
use App\Models\FundedLoan;
use App\Models\FundingApplication;
use App\Models\FundingPartner;
use App\Models\FundingPartnerAssignment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FundingPlatformTest extends TestCase
{
    use RefreshDatabase;

    private Branch $aleppo;
    private Branch $damascus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->aleppo = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();
        $this->damascus = Branch::query()->where('code', 'BR-DAMASCUS')->firstOrFail();
    }

    private function createFundingApplication(Branch $branch, ?User $owner = null, array $overrides = []): FundingApplication
    {
        $owner ??= User::factory()->create([
            'governorate_id' => $branch->governorate_id,
            'branch_id' => $branch->id,
        ]);
        $owner->assignRole('project_owner');

        return FundingApplication::query()->create(array_merge([
            'application_number' => 'FND-TEST-' . uniqid(),
            'applicant_user_id' => $owner->id,
            'applicant_name' => $owner->name,
            'phone' => '0999999999',
            'governorate_id' => $branch->governorate_id,
            'branch_id' => $branch->id,
            'project_name' => 'مشروع اختبار',
            'requested_amount' => 5000000,
            'status' => 'submitted',
            'current_stage' => 'submitted',
            'created_by' => $owner->id,
        ], $overrides));
    }

    public function test_general_director_sees_all_funding_applications(): void
    {
        $this->createFundingApplication($this->aleppo);
        $this->createFundingApplication($this->damascus);

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->getJson('/api/finance/applications?per_page=100')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_branch_manager_sees_own_branch_applications_only(): void
    {
        $this->createFundingApplication($this->aleppo);
        $this->createFundingApplication($this->damascus);

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $response = $this->getJson('/api/finance/applications?per_page=100')->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame((int) $this->aleppo->id, (int) $response->json('data.0.branch_id'));
    }

    public function test_branch_manager_cannot_view_other_branch_application_by_id(): void
    {
        $app = $this->createFundingApplication($this->damascus);

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $this->getJson('/api/finance/applications/' . $app->id)->assertForbidden();
    }

    public function test_project_owner_sees_own_applications_only(): void
    {
        $owner = User::factory()->create(['branch_id' => $this->aleppo->id, 'governorate_id' => $this->aleppo->governorate_id]);
        $owner->assignRole('project_owner');
        $other = User::factory()->create(['branch_id' => $this->damascus->id, 'governorate_id' => $this->damascus->governorate_id]);
        $other->assignRole('project_owner');

        $mine = $this->createFundingApplication($this->aleppo, $owner);
        $this->createFundingApplication($this->damascus, $other);

        Sanctum::actingAs($owner);

        $this->getJson('/api/finance/applications/' . $mine->id)->assertOk();
        $this->getJson('/api/finance/applications?per_page=100')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_consultant_office_sees_assigned_applications_only(): void
    {
        $office = ConsultantOffice::query()->create([
            'name' => 'مكتب اختبار',
            'status' => 'active',
            'created_by' => User::query()->where('email', 'general@system.com')->value('id'),
        ]);

        $consultantUser = User::factory()->create(['consultant_office_id' => $office->id]);
        $consultantUser->assignRole('consultant_office');

        $assigned = $this->createFundingApplication($this->aleppo);
        $other = $this->createFundingApplication($this->damascus);

        ConsultantAssignment::query()->create([
            'funding_application_id' => $assigned->id,
            'consultant_office_id' => $office->id,
            'assigned_by' => User::query()->where('email', 'general@system.com')->value('id'),
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        Sanctum::actingAs($consultantUser);

        $this->getJson('/api/finance/applications/' . $assigned->id)->assertOk();
        $this->getJson('/api/finance/applications/' . $other->id)->assertForbidden();
    }

    public function test_funding_partner_sees_assigned_applications_only(): void
    {
        $partner = FundingPartner::query()->create([
            'name' => 'مصرف اختبار',
            'partner_type' => 'bank',
            'status' => 'active',
            'created_by' => User::query()->where('email', 'general@system.com')->value('id'),
        ]);

        $partnerUser = User::factory()->create(['funding_partner_id' => $partner->id]);
        $partnerUser->assignRole('funding_partner');

        $assigned = $this->createFundingApplication($this->aleppo);
        $other = $this->createFundingApplication($this->damascus);

        FundingPartnerAssignment::query()->create([
            'funding_application_id' => $assigned->id,
            'funding_partner_id' => $partner->id,
            'assigned_by' => User::query()->where('email', 'general@system.com')->value('id'),
            'assigned_at' => now(),
            'status' => 'sent',
        ]);

        Sanctum::actingAs($partnerUser);

        $this->getJson('/api/finance/applications/' . $assigned->id)->assertOk();
        $this->getJson('/api/finance/applications/' . $other->id)->assertForbidden();
    }

    public function test_auditor_is_read_only_on_funding_mutations(): void
    {
        $app = $this->createFundingApplication($this->aleppo, null, ['status' => 'draft']);

        Sanctum::actingAs(User::query()->where('email', 'auditor@system.com')->firstOrFail());

        $this->getJson('/api/finance/applications/' . $app->id)->assertOk();
        $this->postJson('/api/finance/applications/' . $app->id . '/submit')->assertForbidden();
    }

    public function test_create_application_sets_branch_and_governorate(): void
    {
        $owner = User::factory()->create([
            'branch_id' => $this->aleppo->id,
            'governorate_id' => $this->aleppo->governorate_id,
        ]);
        $owner->assignRole('project_owner');
        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/finance/applications', [
            'applicant_name' => $owner->name,
            'project_name' => 'مشروع جديد',
            'requested_amount' => 1000000,
            'governorate_id' => $this->aleppo->governorate_id,
            'branch_id' => $this->aleppo->id,
        ])->assertCreated();

        $this->assertSame((int) $this->aleppo->id, (int) $response->json('data.branch_id'));
        $this->assertSame((int) $this->aleppo->governorate_id, (int) $response->json('data.governorate_id'));
    }

    public function test_assign_consultant_workflow(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $app = $this->createFundingApplication($this->aleppo);
        $office = ConsultantOffice::query()->create([
            'name' => 'مكتب A',
            'status' => 'active',
            'created_by' => $general->id,
        ]);

        Sanctum::actingAs($general);

        $this->postJson('/api/finance/applications/' . $app->id . '/assign-consultant', [
            'consultant_office_id' => $office->id,
        ])->assertCreated();
    }

    public function test_consultant_submits_price_offer(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $office = ConsultantOffice::query()->create(['name' => 'مكتب B', 'status' => 'active', 'created_by' => $general->id]);
        $consultantUser = User::factory()->create(['consultant_office_id' => $office->id]);
        $consultantUser->assignRole('consultant_office');

        $app = $this->createFundingApplication($this->aleppo);
        $assignment = ConsultantAssignment::query()->create([
            'funding_application_id' => $app->id,
            'consultant_office_id' => $office->id,
            'assigned_by' => $general->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        Sanctum::actingAs($consultantUser);

        $this->postJson('/api/finance/consultant-assignments/' . $assignment->id . '/price-offer', [
            'price_offer_amount' => 250000,
        ])->assertOk();
    }

    public function test_consultant_submits_report(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $office = ConsultantOffice::query()->create(['name' => 'مكتب C', 'status' => 'active', 'created_by' => $general->id]);
        $consultantUser = User::factory()->create(['consultant_office_id' => $office->id]);
        $consultantUser->assignRole('consultant_office');
        $app = $this->createFundingApplication($this->aleppo);

        ConsultantAssignment::query()->create([
            'funding_application_id' => $app->id,
            'consultant_office_id' => $office->id,
            'assigned_by' => $general->id,
            'assigned_at' => now(),
            'status' => 'accepted',
        ]);

        Sanctum::actingAs($consultantUser);

        $this->postJson('/api/finance/consultant-reports', [
            'funding_application_id' => $app->id,
            'consultant_office_id' => $office->id,
            'recommendation' => 'approve',
            'report_summary' => 'ملخص',
        ])->assertCreated();
    }

    public function test_assign_partner_and_decision(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $partner = FundingPartner::query()->create(['name' => 'مصرف X', 'status' => 'active', 'created_by' => $general->id]);
        $partnerUser = User::factory()->create(['funding_partner_id' => $partner->id]);
        $partnerUser->assignRole('funding_partner');
        $app = $this->createFundingApplication($this->aleppo, null, ['status' => 'funder_review']);

        Sanctum::actingAs($general);
        $assignmentId = $this->postJson('/api/finance/applications/' . $app->id . '/assign-partner', [
            'funding_partner_id' => $partner->id,
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($partnerUser);
        $this->postJson('/api/finance/partner-assignments/' . $assignmentId . '/decision', [
            'decision' => 'approved',
            'approved_amount' => 4000000,
        ])->assertOk();
    }

    public function test_create_funded_loan_from_approved_application(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $app = $this->createFundingApplication($this->aleppo, null, ['status' => 'approved', 'current_stage' => 'approved']);

        Sanctum::actingAs($general);

        $this->postJson('/api/finance/applications/' . $app->id . '/create-loan', [
            'approved_amount' => 4000000,
            'installment_count' => 12,
            'installment_amount' => 350000,
        ])->assertCreated();

        $this->assertSame('funded', $app->fresh()->status);
    }

    public function test_mark_loan_as_defaulted(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $app = $this->createFundingApplication($this->aleppo, null, ['status' => 'funded']);
        $loan = FundedLoan::query()->create([
            'funding_application_id' => $app->id,
            'loan_number' => 'LN-TEST-' . uniqid(),
            'approved_amount' => 1000000,
            'status' => 'active',
        ]);

        Sanctum::actingAs($general);

        $this->postJson('/api/finance/loans/' . $loan->id . '/mark-defaulted')->assertOk();
        $this->assertSame('defaulted', $loan->fresh()->status);
    }

    public function test_national_metrics_for_general_director(): void
    {
        $this->createFundingApplication($this->aleppo);
        $this->createFundingApplication($this->damascus);

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->getJson('/api/finance/metrics')->assertOk()->assertJsonPath('data.scope', 'national');
    }

    public function test_branch_metrics_for_branch_manager(): void
    {
        $this->createFundingApplication($this->aleppo);
        $this->createFundingApplication($this->damascus);

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $this->getJson('/api/finance/metrics')->assertOk()->assertJsonPath('data.scope', 'branch')
            ->assertJsonPath('data.total_applications', 1);
    }

    public function test_activity_log_records_finance_operations(): void
    {
        $owner = User::factory()->create([
            'branch_id' => $this->aleppo->id,
            'governorate_id' => $this->aleppo->governorate_id,
        ]);
        $owner->assignRole('project_owner');
        Sanctum::actingAs($owner);

        $this->postJson('/api/finance/applications', [
            'applicant_name' => $owner->name,
            'project_name' => 'مشروع سجل',
            'requested_amount' => 500000,
            'governorate_id' => $this->aleppo->governorate_id,
            'branch_id' => $this->aleppo->id,
        ])->assertCreated();

        $this->assertTrue(
            AuditLog::query()->where('action', 'finance_application_created')->where('module', 'finance')->exists()
        );
    }

    public function test_funding_document_upload_is_secured(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create([
            'branch_id' => $this->aleppo->id,
            'governorate_id' => $this->aleppo->governorate_id,
        ]);
        $owner->assignRole('project_owner');
        $app = $this->createFundingApplication($this->aleppo, $owner, ['status' => 'draft']);

        Sanctum::actingAs($owner);

        $this->postJson('/api/finance/applications/' . $app->id . '/documents', [
            'document_type' => 'business_plan',
            'file' => UploadedFile::fake()->create('plan.pdf', 100, 'application/pdf'),
        ])->assertCreated();

        $this->postJson('/api/finance/applications/' . $app->id . '/documents', [
            'document_type' => 'malicious',
            'file' => UploadedFile::fake()->create('evil.php', 10, 'application/x-php'),
        ])->assertStatus(422);
    }

    public function test_submit_application_changes_status_to_submitted(): void
    {
        $owner = User::factory()->create([
            'branch_id' => $this->aleppo->id,
            'governorate_id' => $this->aleppo->governorate_id,
        ]);
        $owner->assignRole('project_owner');
        $app = $this->createFundingApplication($this->aleppo, $owner, ['status' => 'draft', 'current_stage' => 'draft']);

        Sanctum::actingAs($owner);

        $this->postJson('/api/finance/applications/' . $app->id . '/submit')->assertOk();
        $this->assertSame('submitted', $app->fresh()->status);
    }

    public function test_branch_review_approve_moves_to_consultant_review(): void
    {
        $app = $this->createFundingApplication($this->aleppo, null, ['status' => 'submitted']);

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $this->postJson('/api/finance/applications/' . $app->id . '/branch-review', [
            'decision' => 'approve',
        ])->assertOk();

        $this->assertSame('consultant_review', $app->fresh()->status);
    }

    public function test_branch_manager_cannot_access_national_metrics_scope(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $this->getJson('/api/finance/metrics')->assertOk()->assertJsonPath('data.scope', 'branch');
    }

    public function test_idor_access_returns_forbidden(): void
    {
        $owner = User::factory()->create(['branch_id' => $this->aleppo->id, 'governorate_id' => $this->aleppo->governorate_id]);
        $owner->assignRole('project_owner');
        $intruder = User::factory()->create(['branch_id' => $this->damascus->id, 'governorate_id' => $this->damascus->governorate_id]);
        $intruder->assignRole('project_owner');

        $app = $this->createFundingApplication($this->aleppo, $owner);

        Sanctum::actingAs($intruder);

        $this->getJson('/api/finance/applications/' . $app->id)->assertForbidden();
        $this->putJson('/api/finance/applications/' . $app->id, ['project_name' => 'اختراق'])->assertForbidden();
    }
}
