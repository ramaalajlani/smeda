<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Governorate;
use App\Models\Need;
use App\Models\User;
use App\Support\NeedStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NeedsModuleTest extends TestCase
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

    private function createNeed(Branch $branch, ?User $creator = null, array $overrides = []): Need
    {
        $creator ??= User::factory()->create([
            'governorate_id' => $branch->governorate_id,
            'branch_id' => $branch->id,
        ]);

        return Need::query()->create(array_merge([
            'need_code' => 'NEED-TEST-' . uniqid(),
            'title' => 'احتياج اختبار',
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

    private function userWithRole(string $role, Branch $branch): User
    {
        $user = User::factory()->create([
            'governorate_id' => $branch->governorate_id,
            'branch_id' => $branch->id,
        ]);
        $user->assignRole($role);

        return $user;
    }

    /** @return array<string, mixed> */
    private function needCreatePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'احتياج اختبار',
            'description' => 'وصف',
            'sector' => 'زراعة',
            'priority' => 'متوسطة',
            'latitude' => 36.202000,
            'longitude' => 37.134000,
        ], $overrides);
    }

    public function test_general_director_sees_all_needs(): void
    {
        $this->createNeed($this->aleppo);
        $this->createNeed($this->damascus);

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->getJson('/api/needs?per_page=100')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_branch_manager_sees_own_branch_needs_only(): void
    {
        $this->createNeed($this->aleppo);
        $this->createNeed($this->damascus);

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $response = $this->getJson('/api/needs?per_page=100')->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame((int) $this->aleppo->id, (int) $response->json('data.0.branch_id'));
    }

    public function test_branch_manager_cannot_view_other_branch_need_by_id(): void
    {
        $need = $this->createNeed($this->damascus);

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $this->getJson('/api/needs/' . $need->id)->assertForbidden();
    }

    public function test_data_entry_can_create_need(): void
    {
        $user = $this->userWithRole('data_entry', $this->aleppo);
        Sanctum::actingAs($user);

        $this->postJson('/api/needs', $this->needCreatePayload([
            'title' => 'احتياج جديد من إدخال البيانات',
            'priority' => 'عالية',
        ]))->assertCreated()
            ->assertJsonPath('data.title', 'احتياج جديد من إدخال البيانات');
    }

    public function test_auditor_cannot_create_need(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'auditor@system.com')->firstOrFail());

        $this->postJson('/api/needs', [
            'title' => 'محاولة إنشاء',
        ])->assertForbidden();
    }

    public function test_data_reviewer_can_review_need(): void
    {
        $need = $this->createNeed($this->aleppo);
        $reviewer = $this->userWithRole('data_reviewer', $this->aleppo);
        Sanctum::actingAs($reviewer);

        $this->postJson('/api/needs/' . $need->id . '/review', ['note' => 'تم التدقيق'])
            ->assertOk();

        $this->assertSame(NeedStatus::PENDING_BRANCH_APPROVAL, $need->fresh()->status);
    }

    public function test_branch_manager_can_approve_after_review(): void
    {
        $need = $this->createNeed($this->aleppo, null, [
            'status' => NeedStatus::PENDING_BRANCH_APPROVAL,
            'approval_status' => NeedStatus::PENDING_BRANCH_APPROVAL,
        ]);

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $this->postJson('/api/needs/' . $need->id . '/approve', ['note' => 'موافق'])
            ->assertOk();

        $this->assertSame(NeedStatus::APPROVED, $need->fresh()->status);
    }

    public function test_branch_manager_can_reject_need(): void
    {
        $need = $this->createNeed($this->aleppo, null, [
            'status' => NeedStatus::PENDING_BRANCH_APPROVAL,
            'approval_status' => NeedStatus::PENDING_BRANCH_APPROVAL,
        ]);

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $this->postJson('/api/needs/' . $need->id . '/reject', ['rejection_reason' => 'غير مكتمل'])
            ->assertOk();

        $this->assertSame(NeedStatus::REJECTED, $need->fresh()->status);
    }

    public function test_data_reviewer_can_return_need_for_edit(): void
    {
        $need = $this->createNeed($this->aleppo);
        $reviewer = $this->userWithRole('data_reviewer', $this->aleppo);
        Sanctum::actingAs($reviewer);

        $this->postJson('/api/needs/' . $need->id . '/return', ['return_reason' => 'بيانات ناقصة'])
            ->assertOk();

        $this->assertSame(NeedStatus::RETURNED_FOR_EDIT, $need->fresh()->status);
    }

    public function test_project_services_manager_can_classify_approved_need(): void
    {
        $need = $this->createNeed($this->aleppo, null, [
            'status' => NeedStatus::APPROVED,
            'approval_status' => NeedStatus::APPROVED,
        ]);

        $classifier = User::factory()->create();
        $classifier->assignRole('project_services_manager');
        Sanctum::actingAs($classifier);

        $this->postJson('/api/needs/' . $need->id . '/classify', [
            'proposed_intervention' => 'تدريب',
            'note' => 'تصنيف',
        ])->assertOk();

        $this->assertSame(NeedStatus::CLASSIFIED, $need->fresh()->status);
        $this->assertSame('تدريب', $need->fresh()->proposed_intervention);
    }

    public function test_full_workflow_create_review_approve_classify(): void
    {
        $entry = $this->userWithRole('data_entry', $this->aleppo);
        Sanctum::actingAs($entry);

        $create = $this->postJson('/api/needs', $this->needCreatePayload([
            'title' => 'مسار كامل',
            'sector' => 'صناعة',
        ]))->assertCreated();

        $id = (int) $create->json('data.id');

        Sanctum::actingAs($this->userWithRole('data_reviewer', $this->aleppo));
        $this->postJson("/api/needs/{$id}/review")->assertOk();

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());
        $this->postJson("/api/needs/{$id}/approve")->assertOk();

        $classifier = User::factory()->create();
        $classifier->assignRole('project_services_manager');
        Sanctum::actingAs($classifier);
        $this->postJson("/api/needs/{$id}/classify", ['proposed_intervention' => 'تمويل'])->assertOk();

        $need = Need::query()->findOrFail($id);
        $this->assertSame(NeedStatus::CLASSIFIED, $need->status);
    }

    public function test_map_returns_geolocated_points(): void
    {
        $this->createNeed($this->aleppo, null, [
            'latitude' => 36.2,
            'longitude' => 37.15,
            'is_mapped' => true,
        ]);

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $response = $this->getJson('/api/needs/map')->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame(36.2, (float) $response->json('data.0.latitude'));
    }

    public function test_dashboard_returns_stats(): void
    {
        $this->createNeed($this->aleppo);
        $this->createNeed($this->damascus, null, ['need_owner_type' => 'state']);

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $response = $this->getJson('/api/needs/dashboard')->assertOk();
        $this->assertSame(2, (int) $response->json('data.total'));
        $this->assertSame(1, (int) $response->json('data.state'));
    }

    public function test_export_returns_csv(): void
    {
        $this->createNeed($this->aleppo);

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $response = $this->get('/api/needs/export');
        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_lookups_returns_reference_lists(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->getJson('/api/needs/lookups')
            ->assertOk()
            ->assertJsonStructure(['data' => ['sectors', 'need_types', 'priorities', 'statuses']]);
    }

    public function test_legacy_import_skips_duplicates(): void
    {
        Schema::create('gis_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('governorate_name')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        $govName = Governorate::query()->find($this->aleppo->governorate_id)?->name_ar ?? 'حلب';

        \DB::table('gis_survey_responses')->insert([
            'id' => 9001,
            'title' => 'احتياج قديم',
            'governorate_name' => $govName,
            'status' => 'بانتظار تدقيق بيانات المحافظة',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Artisan::call('needs:import-legacy-gis');
        $this->assertSame(1, Need::query()->where('source_platform', 'legacy_gis')->count());

        Artisan::call('needs:import-legacy-gis');
        $this->assertSame(1, Need::query()->where('source_platform', 'legacy_gis')->count());
    }

    public function test_need_create_logs_audit_entry(): void
    {
        $user = $this->userWithRole('data_entry', $this->aleppo);
        Sanctum::actingAs($user);

        $this->postJson('/api/needs', $this->needCreatePayload([
            'title' => 'مع سجل تدقيق',
        ]))->assertCreated();

        $this->assertTrue(
            AuditLog::query()->where('action', 'need_created')->where('module', 'needs')->exists()
        );
    }

    public function test_unauthenticated_cannot_access_needs(): void
    {
        $this->getJson('/api/needs')->assertUnauthorized();
    }
}
