<?php

namespace Tests\Feature;

use App\Models\TrainerRegistrationRequest;
use App\Models\TrainingCenter;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RegistrationRequestOwnerViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_submitter_can_view_own_trainer_registration_request(): void
    {
        $submitter = User::query()->where('email', 'trainer@system.com')->firstOrFail();
        $center = TrainingCenter::query()->firstOrFail();

        $request = TrainerRegistrationRequest::query()->create([
            'request_number' => 'TRR-OWNER-001',
            'training_center_id' => $center->id,
            'full_name' => 'Trainer Owner View',
            'submitted_by_user_id' => $submitter->id,
            'status' => 'pending',
            'branch_id' => $center->branch_id,
            'governorate_id' => $center->governorate_id,
        ]);

        Sanctum::actingAs($submitter);

        $this->getJson('/api/registration-requests/trainers/' . $request->id)
            ->assertOk()
            ->assertJsonPath('data.id', $request->id);
    }

    public function test_user_cannot_view_another_users_trainer_registration_request(): void
    {
        $submitter = User::query()->where('email', 'trainer@system.com')->firstOrFail();
        $other = User::query()->where('email', 'trainee@system.com')->firstOrFail();
        $center = TrainingCenter::query()->firstOrFail();

        $request = TrainerRegistrationRequest::query()->create([
            'request_number' => 'TRR-OTHER-001',
            'training_center_id' => $center->id,
            'full_name' => 'Private Trainer Request',
            'submitted_by_user_id' => $submitter->id,
            'status' => 'pending',
            'branch_id' => $center->branch_id,
            'governorate_id' => $center->governorate_id,
        ]);

        Sanctum::actingAs($other);

        $this->getJson('/api/registration-requests/trainers/' . $request->id)
            ->assertForbidden();
    }

    public function test_reviewer_with_permission_can_view_registration_request(): void
    {
        $submitter = User::query()->where('email', 'trainer@system.com')->firstOrFail();
        $reviewer = User::query()->where('email', 'manager@system.com')->firstOrFail();
        $center = TrainingCenter::query()->firstOrFail();

        $request = TrainerRegistrationRequest::query()->create([
            'request_number' => 'TRR-REVIEW-001',
            'training_center_id' => $center->id,
            'full_name' => 'Reviewer Visible Request',
            'submitted_by_user_id' => $submitter->id,
            'status' => 'pending',
            'branch_id' => $center->branch_id,
            'governorate_id' => $center->governorate_id,
        ]);

        Sanctum::actingAs($reviewer);

        $this->getJson('/api/registration-requests/trainers/' . $request->id)
            ->assertOk();
    }
}
