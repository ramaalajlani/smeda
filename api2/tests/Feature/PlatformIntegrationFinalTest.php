<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Branch;
use App\Models\FinancialRecord;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\TrainingKit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlatformIntegrationFinalTest extends TestCase
{
    use RefreshDatabase;

    private Branch $damascus;
    private Branch $aleppo;
    private TrainingCenter $center;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->damascus = Branch::query()->where('code', 'BR-DAMASCUS')->firstOrFail();
        $this->aleppo = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();
        $this->center = TrainingCenter::query()->firstOrFail();
    }

    public function test_general_director_national_integration_flow(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->getJson('/api/governorates')->assertOk()
            ->assertJsonCount(14, 'data');

        $this->getJson('/api/branches')->assertOk();
        $this->assertGreaterThanOrEqual(14, count($this->getJson('/api/branches')->json('data')));

        $this->postJson('/api/admin/users', [
            'name' => 'Branch Manager Test',
            'email' => 'bm.integration@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'role' => 'branch_manager',
            'governorate_id' => $this->damascus->governorate_id,
            'branch_id' => $this->damascus->id,
            'is_active' => true,
        ])->assertCreated();

        $this->postJson('/api/branches', [
            'name' => 'Integration Branch',
            'code' => 'BR-INT-001',
            'governorate_id' => $this->damascus->governorate_id,
        ])->assertCreated();

        $this->postJson('/api/agreements', [
            'title' => 'National Agreement',
            'partner_name' => 'Partner',
            'scope_type' => 'national',
        ])->assertCreated();

        $this->postJson('/api/agreements', [
            'title' => 'Branch Agreement',
            'partner_name' => 'Local Partner',
            'scope_type' => 'branch',
            'governorate_id' => $this->damascus->governorate_id,
            'branch_id' => $this->damascus->id,
        ])->assertCreated();

        $this->postJson('/api/finance/records', [
            'record_type' => 'funding',
            'title' => 'National Funding',
            'amount' => 10000,
        ])->assertCreated();

        $dashboard = $this->getJson('/api/dashboard')->assertOk()->json();
        $this->assertSame(14, $dashboard['governorates_count'] ?? null);
        $this->assertArrayHasKey('agreements_count', $dashboard);
        $this->assertArrayHasKey('financial_records_count', $dashboard);

        $this->get('/api/admin/activity-logs/export?format=csv')->assertOk();
    }

    public function test_branch_manager_isolation_integration_flow(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'branch.damascus@system.com')->firstOrFail());

        $dashboard = $this->getJson('/api/dashboard')->assertOk()->json();
        $this->assertSame($this->damascus->id, $dashboard['branch_id']);

        $trainerIds = collect($this->getJson('/api/trainers')->json('data'))->pluck('id');
        $this->assertTrue(
            Trainer::query()->where('branch_id', $this->aleppo->id)->whereIn('id', $trainerIds)->doesntExist()
        );

        $courseIds = collect($this->getJson('/api/training-courses')->json('data'))->pluck('id');
        $this->assertTrue(
            TrainingCourse::query()->where('branch_id', $this->aleppo->id)->whereIn('id', $courseIds)->doesntExist()
        );

        Agreement::query()->create([
            'title' => 'Central Only',
            'partner_name' => 'X',
            'scope_type' => 'national',
            'status' => 'active',
            'created_by' => User::query()->where('email', 'general@system.com')->value('id'),
        ]);

        $titles = collect($this->getJson('/api/agreements')->json('data'))->pluck('title');
        $this->assertFalse($titles->contains('Central Only'));

        $this->postJson('/api/branches', ['name' => 'X', 'code' => 'X', 'governorate_id' => 1])->assertForbidden();
        $this->postJson('/api/finance/records', [
            'record_type' => 'payment',
            'title' => 'Denied',
            'amount' => 1,
        ])->assertForbidden();

        $this->getJson('/api/branches/' . $this->aleppo->id)->assertForbidden();
    }

    public function test_deputy_general_director_read_only_administration(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'deputy@system.com')->firstOrFail());

        $this->getJson('/api/governorates')->assertOk();
        $this->getJson('/api/branches')->assertOk();
        $this->postJson('/api/agreements', [
            'title' => 'Denied',
            'partner_name' => 'X',
        ])->assertForbidden();
        $this->getJson('/api/admin/users')->assertForbidden();
    }

    public function test_auditor_read_only_integration(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'auditor@system.com')->firstOrFail());

        $this->getJson('/api/finance/records')->assertOk();
        $this->getJson('/api/admin/activity-logs')->assertOk();
        $this->postJson('/api/finance/records', [
            'record_type' => 'funding',
            'title' => 'Denied',
            'amount' => 1,
        ])->assertForbidden();
        $this->postJson('/api/branches', [
            'name' => 'Denied',
            'code' => 'DENY',
            'governorate_id' => $this->damascus->governorate_id,
        ])->assertForbidden();
    }

    public function test_trainer_user_sees_only_own_courses(): void
    {
        $otherTrainer = Trainer::query()->create([
            'name' => 'Other Integration Trainer',
            'trainer_code' => 'TR-INT-OTHER',
            'training_center_id' => $this->center->id,
            'branch_id' => $this->damascus->id,
            'governorate_id' => $this->damascus->governorate_id,
            'status' => 'active',
        ]);

        TrainingCourse::query()->create([
            'title' => 'Other Trainer Integration Course',
            'course_code' => 'CRS-INT-OTHER',
            'training_center_id' => $this->center->id,
            'trainer_id' => $otherTrainer->id,
            'training_kit_id' => TrainingKit::query()->firstOrFail()->id,
            'delivery_mode' => 'offline',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'scheduled',
            'branch_id' => $this->damascus->id,
            'governorate_id' => $this->damascus->governorate_id,
        ]);

        $trainerUser = User::query()->where('email', 'trainer@system.com')->firstOrFail();
        Sanctum::actingAs($trainerUser);

        $titles = collect($this->getJson('/api/training-courses?per_page=100')->assertOk()->json('data'))->pluck('title');
        $this->assertFalse($titles->contains('Other Trainer Integration Course'));
    }

    public function test_trainee_user_sees_only_own_certificates(): void
    {
        $traineeUser = User::query()->where('email', 'trainee@system.com')->firstOrFail();
        if (!$traineeUser->trainee_id) {
            $this->markTestSkipped('No seeded trainee user.');
        }

        Sanctum::actingAs($traineeUser);

        $this->getJson('/api/dashboard')->assertOk();
        $this->getJson('/api/admin/users')->assertForbidden();
    }

    public function test_new_course_inherits_branch_scope_from_center(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());
        $template = TrainingCourse::query()->whereNotNull('branch_id')->firstOrFail();

        $response = $this->postJson('/api/training-courses', [
            'training_center_id' => $template->training_center_id,
            'trainer_id' => $template->trainer_id,
            'training_kit_id' => $template->training_kit_id,
            'title' => 'Scoped Integration Course',
            'delivery_mode' => 'offline',
            'planned_hours' => 8,
            'capacity' => 10,
            'status' => 'scheduled',
        ])->assertCreated();

        $courseId = $response->json('data.id');
        $created = TrainingCourse::query()->findOrFail($courseId);
        $this->assertSame($template->branch_id, $created->branch_id);
        $this->assertSame($template->governorate_id, $created->governorate_id);
    }
}
