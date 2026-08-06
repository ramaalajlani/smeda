<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Need;
use App\Models\User;
use App\Support\NeedStatus;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\HomsNeedsAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * اختبارات حسابات محافظة حمص (محافظ + مدير فرع):
 * حصر البيانات بمحافظة حمص فقط في الـ Back-end + مسار العمل الكامل.
 */
class HomsNeedsScopeTest extends TestCase
{
    use RefreshDatabase;

    private Branch $homs;
    private Branch $aleppo;
    private User $homsGovernor;
    private User $homsBranchManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(HomsNeedsAccountsSeeder::class);

        $this->homs = Branch::query()->where('code', 'BR-HOMS')->firstOrFail();
        $this->aleppo = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();
        $this->homsGovernor = User::query()->where('email', 'governor.homs@system.com')->firstOrFail();
        $this->homsBranchManager = User::query()->where('email', 'branch.homs@system.com')->firstOrFail();
    }

    private function createNeed(Branch $branch, array $overrides = []): Need
    {
        $creator = User::factory()->create([
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

    /** @return array<string, mixed> إحداثيات داخل حمص */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'احتياج حمص',
            'description' => 'وصف',
            'priority' => 'متوسطة',
            'latitude' => 34.7324,
            'longitude' => 36.7137,
        ], $overrides);
    }

    public function test_homs_accounts_are_linked_and_do_not_use_demo_password(): void
    {
        foreach ([$this->homsGovernor, $this->homsBranchManager] as $user) {
            $this->assertTrue((bool) $user->is_active);
            $this->assertSame((int) $this->homs->governorate_id, (int) $user->governorate_id);
            $this->assertSame((int) $this->homs->id, (int) $user->branch_id);
            $this->assertFalse(Hash::check('12345678', $user->password));
        }

        $this->assertTrue($this->homsGovernor->hasRole('governor'));
        $this->assertTrue($this->homsBranchManager->hasRole('branch_manager'));
    }

    public function test_homs_accounts_have_needs_permissions_but_not_view_all(): void
    {
        foreach ([$this->homsGovernor, $this->homsBranchManager] as $user) {
            foreach (HomsNeedsAccountsSeeder::HOMS_NEEDS_PERMISSIONS as $permission) {
                $this->assertTrue($user->hasPermissionTo($permission), "{$user->email} missing {$permission}");
            }

            $this->assertFalse($user->hasPermissionTo('needs.view_all'), "{$user->email} must not have needs.view_all");
        }
    }

    public function test_homs_permissions_do_not_leak_to_other_governorate_accounts(): void
    {
        $aleppoGovernor = User::query()->where('email', 'governor.aleppo@system.com')->firstOrFail();

        // دور governor العالمي لا يملك الموافقة/الرفض — يجب أن يبقى كذلك
        $this->assertFalse($aleppoGovernor->hasPermissionTo('needs.approve'));
        $this->assertFalse($aleppoGovernor->hasPermissionTo('needs.review'));
    }

    public function test_homs_governor_sees_only_homs_needs(): void
    {
        $homsNeed = $this->createNeed($this->homs);
        $this->createNeed($this->aleppo);

        Sanctum::actingAs($this->homsGovernor);

        $response = $this->getJson('/api/needs?per_page=100')->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($homsNeed->id, (int) $response->json('data.0.id'));
    }

    public function test_homs_branch_manager_sees_only_homs_needs(): void
    {
        $homsNeed = $this->createNeed($this->homs);
        $this->createNeed($this->aleppo);

        Sanctum::actingAs($this->homsBranchManager);

        $response = $this->getJson('/api/needs?per_page=100')->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($homsNeed->id, (int) $response->json('data.0.id'));
    }

    public function test_homs_governor_cannot_access_other_governorate_need_by_id(): void
    {
        $need = $this->createNeed($this->aleppo);

        Sanctum::actingAs($this->homsGovernor);

        $this->getJson('/api/needs/' . $need->id)->assertForbidden();
    }

    public function test_homs_branch_manager_cannot_access_other_governorate_need_by_id(): void
    {
        $need = $this->createNeed($this->aleppo);

        Sanctum::actingAs($this->homsBranchManager);

        $this->getJson('/api/needs/' . $need->id)->assertForbidden();
    }

    public function test_homs_governor_cannot_update_other_governorate_need(): void
    {
        $need = $this->createNeed($this->aleppo);

        Sanctum::actingAs($this->homsGovernor);

        $this->putJson('/api/needs/' . $need->id, ['title' => 'محاولة تعديل'])->assertForbidden();
    }

    public function test_create_need_autofills_homs_scope(): void
    {
        Sanctum::actingAs($this->homsGovernor);

        $response = $this->postJson('/api/needs', $this->payload())->assertCreated();

        $this->assertSame((int) $this->homs->governorate_id, (int) $response->json('data.governorate_id'));
        $this->assertSame((int) $this->homs->id, (int) $response->json('data.branch_id'));
    }

    public function test_create_need_for_other_governorate_is_forbidden(): void
    {
        Sanctum::actingAs($this->homsGovernor);

        $this->postJson('/api/needs', $this->payload([
            'governorate_id' => $this->aleppo->governorate_id,
            'branch_id' => $this->aleppo->id,
        ]))->assertForbidden();
    }

    public function test_full_homs_workflow_create_review_approve_classify_resolve(): void
    {
        // المحافظ ينشئ
        Sanctum::actingAs($this->homsGovernor);
        $id = (int) $this->postJson('/api/needs', $this->payload(['title' => 'مسار حمص الكامل']))
            ->assertCreated()->json('data.id');

        // المحافظ يراجع (يملك needs.review كصلاحية مباشرة)
        $this->postJson("/api/needs/{$id}/review", ['note' => 'تم التدقيق'])->assertOk();
        $this->assertSame(NeedStatus::PENDING_BRANCH_APPROVAL, Need::query()->findOrFail($id)->status);

        // مدير الفرع يعتمد
        Sanctum::actingAs($this->homsBranchManager);
        $this->postJson("/api/needs/{$id}/approve", ['note' => 'موافق'])->assertOk();
        $this->assertSame(NeedStatus::APPROVED, Need::query()->findOrFail($id)->status);

        // مدير الفرع يصنّف (صلاحية مباشرة needs.classify)
        $this->postJson("/api/needs/{$id}/classify", ['proposed_intervention' => 'تدريب'])->assertOk();
        $this->assertSame(NeedStatus::CLASSIFIED, Need::query()->findOrFail($id)->status);

        // مدير الفرع يحل (صلاحية مباشرة needs.resolve)
        $this->postJson("/api/needs/{$id}/resolve", ['note' => 'تم'])->assertOk();
        $this->assertSame(NeedStatus::RESOLVED, Need::query()->findOrFail($id)->status);
    }

    public function test_homs_branch_manager_can_reject_and_return(): void
    {
        Sanctum::actingAs($this->homsBranchManager);

        $toReject = $this->createNeed($this->homs, [
            'status' => NeedStatus::PENDING_BRANCH_APPROVAL,
            'approval_status' => NeedStatus::PENDING_BRANCH_APPROVAL,
        ]);
        $this->postJson("/api/needs/{$toReject->id}/reject", ['rejection_reason' => 'غير مكتمل'])->assertOk();
        $this->assertSame(NeedStatus::REJECTED, $toReject->fresh()->status);

        $toReturn = $this->createNeed($this->homs);
        $this->postJson("/api/needs/{$toReturn->id}/return", ['return_reason' => 'بيانات ناقصة'])->assertOk();
        $this->assertSame(NeedStatus::RETURNED_FOR_EDIT, $toReturn->fresh()->status);
    }

    public function test_homs_map_returns_only_homs_points(): void
    {
        $this->createNeed($this->homs, ['latitude' => 34.73, 'longitude' => 36.71, 'is_mapped' => true]);
        $this->createNeed($this->aleppo, ['latitude' => 36.2, 'longitude' => 37.13, 'is_mapped' => true]);

        Sanctum::actingAs($this->homsGovernor);

        $response = $this->getJson('/api/needs/map')->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('حمص', $response->json('data.0.governorate'));
    }

    public function test_homs_export_contains_only_homs_needs(): void
    {
        $homsNeed = $this->createNeed($this->homs, ['need_code' => 'NEED-HOMS-EXPORT']);
        $aleppoNeed = $this->createNeed($this->aleppo, ['need_code' => 'NEED-ALEPPO-EXPORT']);

        Sanctum::actingAs($this->homsGovernor);

        $response = $this->get('/api/needs/export');
        $response->assertOk();

        $csv = $response->streamedContent();
        $this->assertStringContainsString($homsNeed->need_code, $csv);
        $this->assertStringNotContainsString($aleppoNeed->need_code, $csv);
    }

    public function test_homs_dashboard_is_scoped_to_homs(): void
    {
        $this->createNeed($this->homs);
        $this->createNeed($this->aleppo);

        Sanctum::actingAs($this->homsGovernor);

        $response = $this->getJson('/api/needs/dashboard')->assertOk();
        $this->assertSame(1, (int) $response->json('data.total'));
    }

    public function test_homs_accounts_cannot_manage_national_lookups(): void
    {
        Sanctum::actingAs($this->homsGovernor);
        $this->getJson('/api/needs/lookups/manage')->assertForbidden();

        Sanctum::actingAs($this->homsBranchManager);
        $this->getJson('/api/needs/lookups/manage')->assertForbidden();
    }
}
