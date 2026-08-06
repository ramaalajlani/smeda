<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\CertificateApproval;
use App\Models\DocumentElectronicSignature;
use App\Models\User;
use App\Models\UserElectronicSignature;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\UploadsApproverSignatures;
use Tests\TestCase;

class UserElectronicSignatureTest extends TestCase
{
    use RefreshDatabase;
    use UploadsApproverSignatures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('local');
    }

    public function test_approver_can_upload_and_fetch_own_signature(): void
    {
        $user = User::query()->where('email', 'deputy@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $this->post('/api/my-electronic-signature', [
            'signature' => UploadedFile::fake()->image('sig.png', 280, 90),
        ])->assertCreated();

        $this->getJson('/api/my-electronic-signature')
            ->assertOk()
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.is_active', true);

        $this->get('/api/my-electronic-signature/image')->assertOk();
    }

    public function test_trainee_user_cannot_upload_approver_signature(): void
    {
        $traineeUser = User::query()->where('entity_type', 'trainee_user')->first();
        if (!$traineeUser) {
            $this->markTestSkipped('No trainee user in seed data.');
        }

        Sanctum::actingAs($traineeUser);

        $this->post('/api/my-electronic-signature', [
            'signature' => UploadedFile::fake()->image('sig.png'),
        ])->assertForbidden();
    }

    public function test_replacing_signature_deactivates_previous_record(): void
    {
        $user = User::query()->where('email', 'general@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $this->post('/api/my-electronic-signature', [
            'signature' => UploadedFile::fake()->image('sig1.png'),
        ])->assertCreated();

        $firstId = UserElectronicSignature::query()->where('user_id', $user->id)->orderBy('id')->value('id');

        $this->post('/api/my-electronic-signature', [
            'signature' => UploadedFile::fake()->image('sig2.png'),
        ])->assertCreated();

        $this->assertFalse(UserElectronicSignature::query()->findOrFail($firstId)->is_active);
        $this->assertSame(1, UserElectronicSignature::query()->where('user_id', $user->id)->where('is_active', true)->count());
    }

    public function test_cannot_approve_certificate_without_active_signature(): void
    {
        $certificate = $this->issuePendingCertificate();

        Sanctum::actingAs(User::query()->where('email', 'center@system.com')->firstOrFail());

        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'center_approval',
            'decision' => 'approved',
            'notes' => 'no signature',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['signature']);
    }

    public function test_old_certificate_keeps_old_signature_snapshot_after_user_replaces_signature(): void
    {
        $this->uploadSignaturesForApprovers();

        $certificate = $this->issuePendingCertificate();
        $centerUser = User::query()->where('email', 'center@system.com')->firstOrFail();

        Sanctum::actingAs($centerUser);
        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'center_approval',
            'decision' => 'approved',
            'notes' => 'with signature',
        ])->assertOk();

        $approval = CertificateApproval::query()
            ->where('certificate_id', $certificate->id)
            ->where('approval_step', 'center_approval')
            ->firstOrFail();

        $oldSnapshotPath = $approval->fresh()->electronicSignature?->signature_image_path;
        $oldSnapshotHash = $approval->fresh()->electronicSignature?->signature_image_hash;
        $this->assertNotNull($oldSnapshotPath);

        Sanctum::actingAs($centerUser);
        $this->post('/api/my-electronic-signature', [
            'signature' => UploadedFile::fake()->image('new-signature.png', 400, 120),
        ])->assertCreated();

        $approval->refresh();
        $this->assertSame($oldSnapshotPath, $approval->electronicSignature?->signature_image_path);
        $this->assertSame($oldSnapshotHash, $approval->electronicSignature?->signature_image_hash);
        Storage::disk('local')->assertExists($oldSnapshotPath);
    }

    public function test_document_signature_is_bound_to_signing_user(): void
    {
        $this->uploadSignaturesForApprovers();

        $certificate = $this->issuePendingCertificate();

        Sanctum::actingAs(User::query()->where('email', 'deputy@system.com')->firstOrFail());
        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'center_approval',
            'decision' => 'approved',
        ])->assertForbidden();

        Sanctum::actingAs(User::query()->where('email', 'center@system.com')->firstOrFail());
        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'center_approval',
            'decision' => 'approved',
        ])->assertOk();

        $sig = DocumentElectronicSignature::query()->firstOrFail();
        $this->assertSame(
            User::query()->where('email', 'center@system.com')->value('id'),
            $sig->signed_by_user_id
        );
    }

    public function test_view_certificates_user_cannot_fetch_other_users_snapshot_image(): void
    {
        $this->uploadSignaturesForApprovers();

        $certificate = $this->issuePendingCertificate();

        Sanctum::actingAs(User::query()->where('email', 'center@system.com')->firstOrFail());
        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'center_approval',
            'decision' => 'approved',
        ])->assertOk();

        $snapshotId = DocumentElectronicSignature::query()->value('id');
        $traineeUser = User::query()->where('email', 'trainee@system.com')->firstOrFail();

        Sanctum::actingAs($traineeUser);

        $this->get('/api/electronic-signatures/' . $snapshotId . '/snapshot-image')
            ->assertForbidden();
    }

    public function test_signer_can_fetch_own_snapshot_image(): void
    {
        $this->uploadSignaturesForApprovers();

        $certificate = $this->issuePendingCertificate();
        $centerUser = User::query()->where('email', 'center@system.com')->firstOrFail();

        Sanctum::actingAs($centerUser);
        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'center_approval',
            'decision' => 'approved',
        ])->assertOk();

        $snapshotId = DocumentElectronicSignature::query()->value('id');

        $this->get('/api/electronic-signatures/' . $snapshotId . '/snapshot-image')
            ->assertOk();
    }

    private function issuePendingCertificate(): Certificate
    {
        return $this->issueCertificateViaHttp();
    }
}
