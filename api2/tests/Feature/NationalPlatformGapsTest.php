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

class NationalPlatformGapsTest extends TestCase
{
    use RefreshDatabase;

    private Branch $damascus;
    private Branch $aleppo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->damascus = Branch::query()->where('code', 'BR-DAMASCUS')->firstOrFail();
        $this->aleppo = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();
    }

    public function test_can_assign_manager_user_id_to_branch_via_api(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());
        $manager = User::query()->where('email', 'branch.damascus@system.com')->firstOrFail();

        $this->putJson('/api/branches/' . $this->damascus->id, [
            'manager_user_id' => $manager->id,
        ])->assertOk()
            ->assertJsonPath('data.manager_user_id', $manager->id);
    }

    public function test_assigning_branch_manager_updates_user_branch_scope(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $manager = User::query()->create([
            'name' => 'New Branch Manager',
            'email' => 'new.manager@example.com',
            'password' => bcrypt('12345678'),
            'entity_type' => 'branch_manager',
            'is_active' => true,
        ]);
        $manager->assignRole('branch_manager');

        $this->putJson('/api/branches/' . $this->damascus->id, [
            'manager_user_id' => $manager->id,
        ])->assertOk();

        $manager->refresh();
        $this->assertSame($this->damascus->id, $manager->branch_id);
        $this->assertSame($this->damascus->governorate_id, $manager->governorate_id);
    }

    public function test_cannot_assign_manager_from_different_governorate(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());
        $aleppoManager = User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail();

        $this->putJson('/api/branches/' . $this->damascus->id, [
            'manager_user_id' => $aleppoManager->id,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['manager_user_id']);
    }

    public function test_disable_action_does_not_delete_branch_with_data(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->deleteJson('/api/branches/' . $this->damascus->id)
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('branches', ['id' => $this->damascus->id]);
        $this->assertTrue(AuditLog::query()->where('action', 'branch_disabled')->exists());
    }

    public function test_can_reactivate_disabled_branch(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->damascus->update(['is_active' => false]);

        $this->putJson('/api/branches/' . $this->damascus->id, [
            'name' => $this->damascus->name,
            'code' => $this->damascus->code,
            'governorate_id' => $this->damascus->governorate_id,
            'is_active' => true,
        ])->assertOk()
            ->assertJsonPath('data.is_active', true);

        $this->assertTrue(AuditLog::query()->where('action', 'branch_enabled')->exists());
    }

    public function test_national_agreement_not_visible_to_branch_manager(): void
    {
        Agreement::query()->create([
            'title' => 'اتفاقية مركزية',
            'partner_name' => 'وزارة',
            'agreement_type' => 'general',
            'scope_type' => 'national',
            'status' => 'active',
            'created_by' => User::query()->where('email', 'general@system.com')->value('id'),
        ]);

        Sanctum::actingAs(User::query()->where('email', 'branch.damascus@system.com')->firstOrFail());

        $response = $this->getJson('/api/agreements')->assertOk();
        $this->assertEmpty(collect($response->json('data'))->where('title', 'اتفاقية مركزية'));
    }

    public function test_branch_agreement_visible_to_same_branch_manager_read_only(): void
    {
        $agreement = Agreement::query()->create([
            'title' => 'اتفاقية فرع دمشق',
            'partner_name' => 'شريك محلي',
            'agreement_type' => 'local',
            'scope_type' => 'branch',
            'governorate_id' => $this->damascus->governorate_id,
            'branch_id' => $this->damascus->id,
            'status' => 'active',
            'created_by' => User::query()->where('email', 'general@system.com')->value('id'),
        ]);

        Sanctum::actingAs(User::query()->where('email', 'branch.damascus@system.com')->firstOrFail());

        $this->getJson('/api/agreements/' . $agreement->id)->assertOk();
        $this->putJson('/api/agreements/' . $agreement->id, ['title' => 'تعديل مرفوض'])->assertForbidden();
    }

    public function test_other_branch_manager_cannot_view_branch_agreement(): void
    {
        $agreement = Agreement::query()->create([
            'title' => 'اتفاقية دمشق فقط',
            'partner_name' => 'شريك',
            'scope_type' => 'branch',
            'governorate_id' => $this->damascus->governorate_id,
            'branch_id' => $this->damascus->id,
            'status' => 'active',
            'created_by' => User::query()->where('email', 'general@system.com')->value('id'),
        ]);

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $this->getJson('/api/agreements/' . $agreement->id)->assertForbidden();
    }

    public function test_agreement_show_blocks_unauthorized_access(): void
    {
        $agreement = Agreement::query()->create([
            'title' => 'سرية',
            'partner_name' => 'X',
            'scope_type' => 'national',
            'status' => 'active',
            'created_by' => User::query()->where('email', 'general@system.com')->value('id'),
        ]);

        Sanctum::actingAs(User::query()->where('email', 'branch.damascus@system.com')->firstOrFail());

        $this->getJson('/api/agreements/' . $agreement->id)->assertForbidden();
    }

    public function test_finance_record_show_blocks_other_branch_manager(): void
    {
        $record = FinancialRecord::query()->create([
            'record_type' => 'payment',
            'title' => 'سجل دمشق',
            'amount' => 500,
            'currency' => 'SYP',
            'status' => 'approved',
            'branch_id' => $this->damascus->id,
            'governorate_id' => $this->damascus->governorate_id,
            'created_by' => User::query()->where('email', 'general@system.com')->value('id'),
        ]);

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $this->getJson('/api/finance/records/' . $record->id)->assertForbidden();
    }

    public function test_user_access_resource_includes_branch_and_governorate_names(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@system.com')->firstOrFail());
        $target = User::query()->where('email', 'branch.damascus@system.com')->firstOrFail();

        $this->getJson('/api/admin/users/' . $target->id . '/access')
            ->assertOk()
            ->assertJsonPath('data.branch_id', $this->damascus->id)
            ->assertJsonPath('data.governorate_id', $this->damascus->governorate_id)
            ->assertJsonStructure(['data' => ['branch_name', 'governorate_name', 'permissions_count']]);
    }

    public function test_cannot_create_course_on_disabled_branch(): void
    {
        $template = \App\Models\TrainingCourse::query()
            ->where('branch_id', $this->damascus->id)
            ->firstOrFail();
        $this->damascus->update(['is_active' => false]);

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->postJson('/api/training-courses', [
            'training_center_id' => $template->training_center_id,
            'trainer_id' => $template->trainer_id,
            'training_kit_id' => $template->training_kit_id,
            'title' => 'دورة على فرع معطل',
            'delivery_mode' => 'offline',
            'planned_hours' => 10,
            'capacity' => 20,
            'status' => 'scheduled',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);
    }
}
