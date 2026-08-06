<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\CertificateApproval;
use App\Models\DocumentElectronicSignature;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\TrainingKit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\UploadsApproverSignatures;
use Tests\TestCase;

class CertificateFullApprovalFlowTest extends TestCase
{
    use RefreshDatabase;
    use UploadsApproverSignatures;

    /** @var array<int, string> */
    private const FLOW_STATUSES = [
        'pending_center_approval',
        'pending_training_approval',
        'pending_deputy_approval',
        'pending_general_director_approval',
        'approved',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        \Illuminate\Support\Facades\Storage::fake('local');
        $this->uploadSignaturesForApprovers();
    }

    public function test_issue_certificate_starts_at_pending_center_approval_with_four_approval_rows(): void
    {
        $certificate = $this->issueCertificateViaHttp();

        $this->assertSame('pending_center_approval', $certificate->status);

        $steps = CertificateApproval::query()
            ->where('certificate_id', $certificate->id)
            ->orderBy('id')
            ->pluck('approval_step')
            ->all();

        $this->assertSame([
            'center_approval',
            'training_manager_approval',
            'deputy_director_approval',
            'general_director_approval',
        ], $steps);

        foreach ($steps as $step) {
            $this->assertDatabaseHas('certificate_approvals', [
                'certificate_id' => $certificate->id,
                'approval_step' => $step,
                'decision' => 'pending',
            ]);
        }
    }

    public function test_full_http_approval_flow_transitions_statuses_and_creates_esig_signatures(): void
    {
        $certificate = $this->issueCertificateViaHttp();

        $this->assertSame('pending_center_approval', $certificate->status);

        $this->postApproveAs($certificate, 'center_approval', 'center@system.com')
            ->assertOk()
            ->assertJsonPath('data.status', 'pending_training_approval');

        $certificate->refresh();
        $this->assertSame('pending_training_approval', $certificate->status);

        $this->postApproveAs($certificate, 'training_manager_approval', 'manager@system.com')
            ->assertOk()
            ->assertJsonPath('data.status', 'pending_deputy_approval');

        $certificate->refresh();
        $this->assertSame('pending_deputy_approval', $certificate->status);

        $this->postApproveAs($certificate, 'deputy_director_approval', 'deputy@system.com')
            ->assertOk()
            ->assertJsonPath('data.status', 'pending_general_director_approval');

        $certificate->refresh();
        $this->assertSame('pending_general_director_approval', $certificate->status);

        $deputySig = DocumentElectronicSignature::query()
            ->whereHasMorph('signable', [CertificateApproval::class], function ($q) use ($certificate) {
                $q->where('certificate_id', $certificate->id)
                    ->where('approval_step', 'deputy_director_approval');
            })
            ->first();

        $this->assertNotNull($deputySig);
        $this->assertStringStartsWith('ESIG-', $deputySig->verification_code);

        $this->postApproveAs($certificate, 'general_director_approval', 'general@system.com')
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $certificate->refresh();
        $this->assertSame('approved', $certificate->status);
        $this->assertTrue((bool) $certificate->is_verified);

        $generalSig = DocumentElectronicSignature::query()
            ->whereHasMorph('signable', [CertificateApproval::class], function ($q) use ($certificate) {
                $q->where('certificate_id', $certificate->id)
                    ->where('approval_step', 'general_director_approval');
            })
            ->first();

        $this->assertNotNull($generalSig);
        $this->assertStringStartsWith('ESIG-', $generalSig->verification_code);

        $this->getJson('/api/signatures/verify/' . $deputySig->verification_code)->assertOk()->assertJsonPath('valid', true);
        $this->getJson('/api/signatures/verify/' . $generalSig->verification_code)->assertOk()->assertJsonPath('valid', true);
    }

    public function test_backend_status_enum_uses_pending_training_approval_not_manager_suffix(): void
    {
        $certificate = $this->issueCertificateViaHttp();
        $this->postApproveAs($certificate, 'center_approval', 'center@system.com')->assertOk();

        $certificate->refresh();
        $this->assertSame('pending_training_approval', $certificate->status);
        $this->assertNotSame('pending_training_manager_approval', $certificate->status);
        $this->assertContains('pending_training_approval', self::FLOW_STATUSES);
        $this->assertNotContains('pending_training_manager_approval', self::FLOW_STATUSES);
    }

    public function test_general_director_with_only_gd_approve_permission_passes_route_middleware(): void
    {
        $certificate = $this->advanceToGeneralDirectorQueue();

        $gdOnly = User::factory()->create([
            'email' => 'gdonly@test.local',
            'is_active' => true,
            'entity_type' => 'admin',
        ]);
        $gdOnly->syncRoles(['auditor']);
        $gdOnly->givePermissionTo('approve_general_director_certificates');

        $this->assertTrue($gdOnly->hasPermissionTo('approve_general_director_certificates'));
        $this->assertFalse($gdOnly->hasPermissionTo('approve_center_certificates'));
        $this->assertFalse($gdOnly->hasPermissionTo('approve_training_certificates'));
        $this->assertFalse($gdOnly->hasPermissionTo('approve_deputy_certificates'));

        $this->uploadSignatureForUser($gdOnly, 'gdonly.png');

        Sanctum::actingAs($gdOnly);

        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'general_director_approval',
            'decision' => 'approved',
            'notes' => 'GD only middleware test',
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved');
    }

    public function test_center_user_cannot_approve_general_director_step(): void
    {
        $certificate = $this->advanceToGeneralDirectorQueue();

        Sanctum::actingAs(User::query()->where('email', 'center@system.com')->firstOrFail());

        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'general_director_approval',
            'decision' => 'approved',
            'notes' => 'should fail',
        ])->assertForbidden();
    }

    public function test_deputy_cannot_approve_center_step_when_certificate_is_pending_center(): void
    {
        $certificate = $this->issueCertificateViaHttp();

        Sanctum::actingAs(User::query()->where('email', 'deputy@system.com')->firstOrFail());

        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'center_approval',
            'decision' => 'approved',
            'notes' => 'should fail',
        ])->assertForbidden();
    }

    public function test_dashboard_returns_certificates_pending_general_director_for_general_director(): void
    {
        $pending = $this->advanceToGeneralDirectorQueue();

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonStructure(['certificates_pending_general_director'])
            ->assertJsonPath('dashboard_role', 'general_director');

        $count = (int) $this->getJson('/api/dashboard')->json('certificates_pending_general_director');
        $this->assertGreaterThanOrEqual(1, $count);

        $this->assertSame(
            'pending_general_director_approval',
            Certificate::query()->findOrFail($pending->id)->status
        );
    }

    public function test_signature_verify_api_returns_signer_and_certificate_fields(): void
    {
        $certificate = $this->advanceToGeneralDirectorQueue();

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());
        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'general_director_approval',
            'decision' => 'approved',
            'notes' => 'final',
        ])->assertOk();

        $sig = DocumentElectronicSignature::query()
            ->whereHasMorph('signable', [CertificateApproval::class], function ($q) use ($certificate) {
                $q->where('certificate_id', $certificate->id)
                    ->where('approval_step', 'general_director_approval');
            })
            ->firstOrFail();

        $this->getJson('/api/signatures/verify/' . $sig->verification_code)
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonStructure([
                'signer_name',
                'signer_title',
                'role_label',
                'signed_at',
                'certificate_code',
                'certificate_number',
            ])
            ->assertJsonPath('certificate_code', $certificate->certificate_code);
    }

    public function test_user_with_only_gd_permission_cannot_approve_center_step(): void
    {
        $certificate = $this->issueCertificateViaHttp();

        $gdOnly = User::factory()->create([
            'email' => 'gdonly-center@test.local',
            'is_active' => true,
            'entity_type' => 'admin',
        ]);
        $gdOnly->syncRoles(['auditor']);
        $gdOnly->givePermissionTo('approve_general_director_certificates');

        Sanctum::actingAs($gdOnly);

        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'center_approval',
            'decision' => 'approved',
            'notes' => 'should fail middleware',
        ])->assertForbidden();
    }

    public function test_training_manager_cannot_approve_deputy_step(): void
    {
        $certificate = $this->issueCertificateViaHttp();
        $this->postApproveAs($certificate, 'center_approval', 'center@system.com')->assertOk();
        $this->postApproveAs($certificate, 'training_manager_approval', 'manager@system.com')->assertOk();

        Sanctum::actingAs(User::query()->where('email', 'manager@system.com')->firstOrFail());

        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'deputy_director_approval',
            'decision' => 'approved',
            'notes' => 'should fail',
        ])->assertForbidden();
    }

    public function test_with_approvals_includes_electronic_signature_for_executive_steps(): void
    {
        $certificate = $this->advanceToGeneralDirectorQueue();

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());
        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'general_director_approval',
            'decision' => 'approved',
            'notes' => 'final',
        ])->assertOk();

        Sanctum::actingAs(User::query()->where('email', 'center@system.com')->firstOrFail());

        $response = $this->getJson('/api/certificates?with_approvals=1&status=approved&per_page=5')
            ->assertOk();

        $row = collect($response->json('data'))
            ->firstWhere('id', $certificate->id);

        $this->assertNotNull($row);

        $deputyApproval = collect($row['approvals'] ?? [])
            ->firstWhere('approval_step', 'deputy_director_approval');
        $generalApproval = collect($row['approvals'] ?? [])
            ->firstWhere('approval_step', 'general_director_approval');

        $this->assertSame('approved', $deputyApproval['decision'] ?? null);
        $this->assertSame('approved', $generalApproval['decision'] ?? null);
        $this->assertStringStartsWith('ESIG-', $deputyApproval['electronic_signature']['verification_code'] ?? '');
        $this->assertStringStartsWith('ESIG-', $generalApproval['electronic_signature']['verification_code'] ?? '');
    }

    public function test_all_four_approvals_are_approved_after_full_flow(): void
    {
        $certificate = $this->issueCertificateViaHttp();

        $this->postApproveAs($certificate, 'center_approval', 'center@system.com')->assertOk();
        $this->postApproveAs($certificate, 'training_manager_approval', 'manager@system.com')->assertOk();
        $this->postApproveAs($certificate, 'deputy_director_approval', 'deputy@system.com')->assertOk();
        $this->postApproveAs($certificate, 'general_director_approval', 'general@system.com')->assertOk();

        $decisions = CertificateApproval::query()
            ->where('certificate_id', $certificate->id)
            ->orderBy('id')
            ->pluck('decision')
            ->all();

        $this->assertSame(['approved', 'approved', 'approved', 'approved'], $decisions);
        $this->assertSame('approved', $certificate->fresh()->status);
    }

    public function test_print_view_includes_signature_snapshots_for_all_approvals(): void
    {
        $certificate = $this->issueCertificateViaHttp();

        $this->postApproveAs($certificate, 'center_approval', 'center@system.com')->assertOk();
        $this->postApproveAs($certificate, 'training_manager_approval', 'manager@system.com')->assertOk();
        $this->postApproveAs($certificate, 'deputy_director_approval', 'deputy@system.com')->assertOk();
        $this->postApproveAs($certificate, 'general_director_approval', 'general@system.com')->assertOk();

        $certificate->refresh();
        $html = $this->get('/certificates/' . urlencode((string) $certificate->certificate_code) . '/print')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('sign-image', $html);
        $this->assertStringContainsString('ESIG-', $html);
    }

    private function advanceToGeneralDirectorQueue(): Certificate
    {
        $certificate = $this->issueCertificateViaHttp();

        $this->postApproveAs($certificate, 'center_approval', 'center@system.com')->assertOk();
        $this->postApproveAs($certificate, 'training_manager_approval', 'manager@system.com')->assertOk();
        $this->postApproveAs($certificate, 'deputy_director_approval', 'deputy@system.com')->assertOk();

        return $certificate->fresh();
    }

    private function postApproveAs(Certificate $certificate, string $step, string $email)
    {
        Sanctum::actingAs(User::query()->where('email', $email)->firstOrFail());

        return $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => $step,
            'decision' => 'approved',
            'notes' => 'flow test',
        ]);
    }
}
