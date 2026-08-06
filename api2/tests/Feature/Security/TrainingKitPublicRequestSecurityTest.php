<?php

namespace Tests\Feature\Security;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingKitPublicRequestSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_validation_rejects_missing_required_fields(): void
    {
        $this->postJson('/api/training-kit-public-requests', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['applicant_name', 'applicant_email', 'proposed_name']);
    }

    public function test_valid_request_is_stored(): void
    {
        $this->postJson('/api/training-kit-public-requests', [
            'applicant_name' => 'Applicant',
            'applicant_email' => 'applicant@example.com',
            'proposed_name' => 'Kit Name',
            'city' => 'Damascus',
        ])->assertCreated();

        $this->assertDatabaseHas('training_kit_public_requests', [
            'applicant_email' => 'applicant@example.com',
            'status' => 'pending',
        ]);
    }
}
