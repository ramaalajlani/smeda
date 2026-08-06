<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Certificate;
use App\Models\TrainerRegistrationRequest;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\User;
use App\Support\RoleLabel;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RemainingNotesClosureTest extends TestCase
{
    use RefreshDatabase;

    private Branch $damascus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->damascus = Branch::query()->where('code', 'BR-DAMASCUS')->firstOrFail();
    }

    public function test_training_course_resource_includes_branch_scope_fields(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $course = TrainingCourse::query()->whereNotNull('branch_id')->firstOrFail();
        $branch = Branch::query()->findOrFail($course->branch_id);

        $this->getJson('/api/training-courses?per_page=5')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'branch_id',
                    'branch_name',
                    'governorate_id',
                    'governorate_name',
                ]],
            ]);

        $row = collect($this->getJson('/api/training-courses?per_page=100')->json('data'))
            ->firstWhere('id', $course->id);

        $this->assertSame($branch->id, $row['branch_id']);
        $this->assertSame($branch->name, $row['branch_name']);
        $this->assertNotNull($row['governorate_id']);
        $this->assertNotEmpty($row['governorate_name']);
    }

    public function test_certificate_resource_includes_branch_scope_fields(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $certificate = Certificate::query()->whereNotNull('branch_id')->firstOrFail();
        $branch = Branch::query()->findOrFail($certificate->branch_id);

        $row = collect($this->getJson('/api/certificates?per_page=100')->assertOk()->json('data'))
            ->firstWhere('id', $certificate->id);

        $this->assertNotNull($row);
        $this->assertSame($branch->id, $row['branch_id']);
        $this->assertSame($branch->name, $row['branch_name']);
        $this->assertNotNull($row['governorate_id']);
        $this->assertNotEmpty($row['governorate_name']);
    }

    public function test_registration_request_models_have_branch_and_governorate_relations(): void
    {
        $center = TrainingCenter::query()->firstOrFail();

        $request = TrainerRegistrationRequest::query()->create([
            'request_number' => 'TRR-SCOPE-TEST',
            'training_center_id' => $center->id,
            'full_name' => 'Scope Test Trainer',
            'submitted_by_user_id' => User::query()->where('email', 'general@system.com')->value('id'),
            'status' => 'pending',
            'branch_id' => $this->damascus->id,
            'governorate_id' => $this->damascus->governorate_id,
        ]);

        $this->assertTrue(method_exists($request, 'branch'));
        $this->assertTrue(method_exists($request, 'governorate'));
        $this->assertSame($this->damascus->id, $request->branch()->first()?->id);
        $this->assertSame($this->damascus->governorate_id, $request->governorate()->first()?->id);
    }

    public function test_trainer_registration_api_returns_branch_scope_fields(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());
        $center = TrainingCenter::query()->firstOrFail();

        TrainerRegistrationRequest::query()->create([
            'request_number' => 'TRR-API-SCOPE',
            'training_center_id' => $center->id,
            'full_name' => 'API Scope Trainer',
            'submitted_by_user_id' => User::query()->where('email', 'general@system.com')->value('id'),
            'status' => 'pending',
            'branch_id' => $this->damascus->id,
            'governorate_id' => $this->damascus->governorate_id,
        ]);

        $row = collect($this->getJson('/api/registration-requests/trainers?per_page=50')->assertOk()->json('data'))
            ->firstWhere('request_number', 'TRR-API-SCOPE');

        $this->assertNotNull($row);
        $this->assertSame($this->damascus->id, $row['branch_id']);
        $this->assertSame($this->damascus->name, $row['branch_name']);
        $this->assertSame($this->damascus->governorate_id, $row['governorate_id']);
        $this->assertNotEmpty($row['governorate_name']);
    }

    public function test_role_label_maps_known_roles_and_preserves_unknown(): void
    {
        $this->assertSame('المدير العام', RoleLabel::label('general_director'));
        $this->assertSame('مدير فرع', RoleLabel::label('branch_manager'));
        $this->assertSame('مدرب', RoleLabel::label('trainer_user'));
        $this->assertSame('custom_role', RoleLabel::label('custom_role'));
        $this->assertSame(['المدير العام', 'مدقق'], RoleLabel::labels(['general_director', 'auditor']));
    }
}
