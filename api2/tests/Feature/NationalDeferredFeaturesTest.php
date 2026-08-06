<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\FinancialRecord;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NationalDeferredFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private Branch $damascus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->damascus = Branch::query()->where('code', 'BR-DAMASCUS')->firstOrFail();
    }

    public function test_general_director_can_create_branch(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->postJson('/api/branches', [
            'name' => 'فرع تجريبي',
            'code' => 'BR-TEST-001',
            'governorate_id' => $this->damascus->governorate_id,
            'is_active' => true,
        ])->assertCreated()
            ->assertJsonPath('data.code', 'BR-TEST-001');

        $this->assertTrue(AuditLog::query()->where('action', 'branch_created')->exists());
    }

    public function test_branch_manager_cannot_create_branch(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'branch.damascus@system.com')->firstOrFail());

        $this->postJson('/api/branches', [
            'name' => 'فرع مرفوض',
            'code' => 'BR-DENY',
            'governorate_id' => $this->damascus->governorate_id,
        ])->assertForbidden();
    }

    public function test_branch_with_data_cannot_be_deleted_without_safe_handling(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->deleteJson('/api/branches/' . $this->damascus->id)
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('branches', ['id' => $this->damascus->id, 'is_active' => false]);
        $this->assertTrue(AuditLog::query()->where('action', 'branch_disabled')->exists());
    }

    public function test_activity_log_csv_export_works(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@system.com')->firstOrFail());

        $response = $this->get('/api/admin/activity-logs/export?format=csv');
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('التاريخ', $response->streamedContent());
        $this->assertTrue(AuditLog::query()->where('action', 'activity_logs_exported')->exists());
    }

    public function test_general_director_can_create_agreement(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->postJson('/api/agreements', [
            'title' => 'اتفاقية وطنية',
            'partner_name' => 'جهة شريكة',
            'agreement_type' => 'cooperation',
            'status' => 'draft',
        ])->assertCreated()
            ->assertJsonPath('data.title', 'اتفاقية وطنية');

        $this->assertTrue(AuditLog::query()->where('action', 'agreement_created')->exists());
    }

    public function test_deputy_cannot_manage_agreements_without_permission(): void
    {
        $deputy = User::query()->where('email', 'deputy@system.com')->firstOrFail();
        Sanctum::actingAs($deputy);

        $this->postJson('/api/agreements', [
            'title' => 'اتفاقية نائب',
            'partner_name' => 'شريك',
        ])->assertForbidden();

        $this->getJson('/api/agreements')->assertOk();
    }

    public function test_branch_manager_cannot_view_central_agreements(): void
    {
        Agreement::query()->create([
            'title' => 'اتفاقية سرية',
            'partner_name' => 'مركز',
            'agreement_type' => 'general',
            'scope_type' => 'national',
            'status' => 'active',
            'created_by' => User::query()->where('email', 'general@system.com')->value('id'),
        ]);

        Sanctum::actingAs(User::query()->where('email', 'branch.damascus@system.com')->firstOrFail());

        $response = $this->getJson('/api/agreements')->assertOk();
        $this->assertEmpty(collect($response->json('data'))->where('title', 'اتفاقية سرية'));
    }

    public function test_general_director_can_create_financial_record(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->postJson('/api/finance/records', [
            'record_type' => 'funding',
            'title' => 'تمويل مشروع',
            'amount' => 50000,
            'currency' => 'SYP',
            'branch_id' => $this->damascus->id,
            'governorate_id' => $this->damascus->governorate_id,
        ])->assertCreated()
            ->assertJsonPath('data.title', 'تمويل مشروع');

        $this->assertTrue(AuditLog::query()->where('action', 'financial_record_created')->exists());
    }

    public function test_branch_manager_sees_only_own_branch_financial_records(): void
    {
        $aleppo = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();

        FinancialRecord::query()->create([
            'record_type' => 'payment',
            'title' => 'دفع دمشق',
            'amount' => 1000,
            'currency' => 'SYP',
            'status' => 'approved',
            'branch_id' => $this->damascus->id,
            'governorate_id' => $this->damascus->governorate_id,
            'created_by' => User::query()->where('email', 'general@system.com')->value('id'),
        ]);

        FinancialRecord::query()->create([
            'record_type' => 'payment',
            'title' => 'دفع حلب',
            'amount' => 2000,
            'currency' => 'SYP',
            'status' => 'approved',
            'branch_id' => $aleppo->id,
            'governorate_id' => $aleppo->governorate_id,
            'created_by' => User::query()->where('email', 'general@system.com')->value('id'),
        ]);

        Sanctum::actingAs(User::query()->where('email', 'branch.damascus@system.com')->firstOrFail());

        $response = $this->getJson('/api/finance/records')->assertOk();
        $titles = collect($response->json('data'))->pluck('title')->all();

        $this->assertContains('دفع دمشق', $titles);
        $this->assertNotContains('دفع حلب', $titles);
    }

    public function test_auditor_is_read_only_for_finance(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'auditor@system.com')->firstOrFail());

        $this->getJson('/api/finance/records')->assertOk();

        $this->postJson('/api/finance/records', [
            'record_type' => 'funding',
            'title' => 'محاولة مدق',
            'amount' => 100,
        ])->assertForbidden();
    }

    public function test_sync_roles_with_branch_manager_requires_branch_id(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        $target = User::query()->where('email', 'auditor@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users/' . $target->id . '/roles/sync', [
            'roles' => ['branch_manager'],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);

        $this->postJson('/api/admin/users/' . $target->id . '/roles/sync', [
            'roles' => ['branch_manager'],
            'governorate_id' => $this->damascus->governorate_id,
            'branch_id' => $this->damascus->id,
        ])->assertOk()
            ->assertJsonPath('data.branch_id', $this->damascus->id)
            ->assertJsonPath('data.governorate_id', $this->damascus->governorate_id);
    }
}
