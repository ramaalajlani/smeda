<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\TrainingCenter;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TrainingBackendHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_trainee_user_cannot_list_trainees(): void
    {
        $user = User::query()->where('email', 'trainee@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $this->getJson('/api/trainees')->assertForbidden();
    }

    public function test_admin_can_list_trainees(): void
    {
        $user = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $this->getJson('/api/trainees')->assertOk();
    }

    public function test_center_user_cannot_view_other_center(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $otherCenterId = TrainingCenter::query()
            ->where('id', '!=', $user->training_center_id)
            ->value('id');

        $this->assertNotNull($otherCenterId);

        $this->getJson('/api/training-centers/' . $otherCenterId)->assertNotFound();
    }

    public function test_certificate_verify_does_not_expose_sensitive_fields_for_pending_certificate(): void
    {
        $certificate = Certificate::query()->firstOrFail();
        $certificate->update([
            'status' => 'pending_center_approval',
            'is_verified' => false,
        ]);

        $response = $this->postJson('/api/certificates/verify', [
            'type' => 'certificate_number',
            'value' => $certificate->certificate_number,
        ]);

        $response->assertForbidden();
        $response->assertJsonMissing(['verification_code', 'national_id', 'phone', 'email']);
        $response->assertJsonStructure([
            'message',
            'data' => ['certificate_number', 'status', 'is_verified'],
        ]);
    }

    public function test_certificate_verify_does_not_expose_sensitive_fields_for_approved_certificate(): void
    {
        $certificate = Certificate::query()
            ->where('status', 'approved')
            ->where('is_verified', true)
            ->firstOrFail();

        $response = $this->postJson('/api/certificates/verify', [
            'type' => 'certificate_number',
            'value' => $certificate->certificate_number,
        ]);

        $response->assertOk();
        $response->assertJsonMissing(['verification_code', 'national_id', 'phone', 'email', 'anti_fake_hash']);
        $response->assertJsonStructure([
            'data' => [
                'certificate_number',
                'certificate_type',
                'trainee_name',
                'status',
            ],
        ]);
    }

    public function test_issue_certificate_rejects_client_issued_by(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $this->postJson('/api/certificates/issue', [
            'training_course_id' => 1,
            'trainee_id' => 1,
            'certificate_type' => 'attendance',
            'issued_by' => 999,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['issued_by']);
    }

    public function test_approve_certificate_rejects_client_approved_by(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $certificate = Certificate::query()->firstOrFail();
        $certificate->update(['status' => 'pending_center_approval']);

        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'center_approval',
            'decision' => 'approved',
            'approved_by' => 999,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['approved_by']);
    }

    public function test_register_rejects_admin_role_from_client(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Bad Actor',
            'email' => 'badactor@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'account_type' => 'trainee',
            'role' => 'admin',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }
}
