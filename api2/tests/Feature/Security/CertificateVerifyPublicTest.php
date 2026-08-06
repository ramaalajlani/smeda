<?php

namespace Tests\Feature\Security;

use App\Models\Certificate;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateVerifyPublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_certificate_verify_works_without_token(): void
    {
        $certificate = Certificate::query()
            ->where('status', 'approved')
            ->where('is_verified', true)
            ->firstOrFail();

        $this->postJson('/api/certificates/verify', [
            'type' => 'certificate_number',
            'value' => $certificate->certificate_number,
        ])->assertOk()
            ->assertJsonPath('data.certificate_number', $certificate->certificate_number);
    }

    public function test_certificate_verify_invalid_code_returns_safe_response(): void
    {
        $response = $this->postJson('/api/certificates/verify', [
            'type' => 'certificate_number',
            'value' => 'NON-EXISTENT-CERT-99999',
        ]);

        $response->assertNotFound();
        $response->assertJsonMissing(['national_id', 'phone', 'email', 'verification_code', 'anti_fake_hash']);
    }
}
