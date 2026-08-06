<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\DocumentElectronicSignature;
use App\Models\User;
use App\Services\Training\CertificateApprovalService;
use App\DTOs\Training\ApproveCertificateData;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\UploadsApproverSignatures;
use Tests\TestCase;

class ExecutiveElectronicSignatureTest extends TestCase
{
    use RefreshDatabase;
    use UploadsApproverSignatures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('local');
        $this->uploadSignaturesForApprovers();
    }

    public function test_deputy_and_general_director_approvals_create_verifiable_electronic_signatures(): void
    {
        $certificate = $this->issueCertificatePendingApproval();

        $this->approveStep($certificate, 'center_approval', 'center@system.com');
        $this->approveStep($certificate, 'training_manager_approval', 'manager@system.com');
        $this->approveStep($certificate, 'deputy_director_approval', 'deputy@system.com');

        $certificate = $certificate->fresh(['approvals.electronicSignature']);
        $this->assertSame('pending_general_director_approval', $certificate->status);

        $deputySignature = $certificate->approvals
            ->firstWhere('approval_step', 'deputy_director_approval')
            ?->electronicSignature;

        $this->assertNotNull($deputySignature);
        $this->assertStringStartsWith('ESIG-', $deputySignature->verification_code);
        $this->assertNotNull($deputySignature->signature_image_path);

        $verifyDeputy = $this->getJson('/api/signatures/verify/' . $deputySignature->verification_code);
        $verifyDeputy->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('role_key', 'deputy_general_director');

        $this->approveStep($certificate, 'general_director_approval', 'general@system.com');

        $certificate = $certificate->fresh(['approvals.electronicSignature']);
        $this->assertSame('approved', $certificate->status);

        $generalSignature = $certificate->approvals
            ->firstWhere('approval_step', 'general_director_approval')
            ?->electronicSignature;

        $this->assertNotNull($generalSignature);
        $this->assertStringStartsWith('ESIG-', $generalSignature->verification_code);

        $this->getJson('/api/signatures/verify/' . $generalSignature->verification_code)
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('role_key', 'general_director');
    }

    public function test_tampered_signature_hmac_is_rejected_by_verify_api(): void
    {
        $certificate = $this->issueCertificatePendingApproval();

        $this->approveStep($certificate, 'center_approval', 'center@system.com');
        $this->approveStep($certificate, 'training_manager_approval', 'manager@system.com');
        $this->approveStep($certificate, 'deputy_director_approval', 'deputy@system.com');

        $signature = DocumentElectronicSignature::query()->firstOrFail();
        $signature->update(['signature_hmac' => str_repeat('a', 64)]);

        $this->getJson('/api/signatures/verify/' . $signature->verification_code)
            ->assertNotFound()
            ->assertJsonPath('valid', false);
    }

    public function test_unknown_verification_code_returns_not_found(): void
    {
        $this->getJson('/api/signatures/verify/ESIG-XXXX-YYYY')
            ->assertNotFound()
            ->assertJsonPath('valid', false);
    }

    private function issueCertificatePendingApproval(): Certificate
    {
        return $this->issueCertificateViaHttp();
    }

    private function approveStep(Certificate $certificate, string $step, string $email): void
    {
        $approver = User::query()->where('email', $email)->firstOrFail();
        Sanctum::actingAs($approver);

        app(CertificateApprovalService::class)->approve(
            $certificate->fresh(),
            new ApproveCertificateData(
                approvalStep: $step,
                decision: 'approved',
                notes: 'test approval',
            ),
            $approver
        );
    }
}
