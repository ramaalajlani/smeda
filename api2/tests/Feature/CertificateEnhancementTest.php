<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\TrainingKit;
use App\Models\User;
use App\Services\Training\CertificateApprovalService;
use App\Services\Training\CertificateCodeGenerator;
use App\Services\Training\CertificateService;
use App\Support\CertificateType;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\UploadsApproverSignatures;
use Tests\TestCase;

class CertificateEnhancementTest extends TestCase
{
    use RefreshDatabase;
    use UploadsApproverSignatures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('local');
    }

    public function test_issue_attendance_certificate_generates_unique_code(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        [$course, $trainee] = $this->prepareCompletedCourse($user);

        Certificate::query()
            ->where('training_course_id', $course->id)
            ->where('trainee_id', $trainee->id)
            ->where('certificate_type', 'attendance')
            ->delete();

        $course->trainees()->updateExistingPivot($trainee->id, [
            'attendance_status' => 'attended',
            'attended_hours' => 8,
            'result' => 'pending',
        ]);

        $response = $this->postJson('/api/certificates/issue', [
            'training_course_id' => $course->id,
            'trainee_id' => $trainee->id,
            'certificate_type' => 'attendance',
            'hours_awarded' => 8,
        ])->assertCreated();

        $code = $response->json('data.certificate_code');
        $this->assertNotEmpty($code);
        $this->assertSame('attendance', $response->json('data.certificate_type'));
        $this->assertDatabaseHas('certificates', [
            'certificate_code' => $code,
            'certificate_type' => 'attendance',
            'training_hours' => 8,
        ]);
    }

    public function test_issue_completion_certificate_for_passed_trainee(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        [$course, $trainee] = $this->prepareCompletedCourse($user);

        Certificate::query()
            ->where('training_course_id', $course->id)
            ->where('trainee_id', $trainee->id)
            ->whereIn('certificate_type', ['pass', 'completion'])
            ->delete();

        $course->trainees()->updateExistingPivot($trainee->id, [
            'attendance_status' => 'attended',
            'attended_hours' => 10,
            'result' => 'passed',
            'score' => 88,
        ]);

        $response = $this->postJson('/api/certificates/issue', [
            'training_course_id' => $course->id,
            'trainee_id' => $trainee->id,
            'certificate_type' => 'completion',
            'score' => 88,
        ])->assertCreated();

        $this->assertSame('pass', $response->json('data.certificate_type'));
        $this->assertSame('شهادة اجتياز تدريب', $response->json('data.certificate_type_label'));
    }

    public function test_certificate_code_follows_center_trainer_kit_course_trainee_sequence(): void
    {
        $generator = app(CertificateCodeGenerator::class);

        $code = $generator->buildCertificateCode('TC-001', 'TRN-001', 'KIT-001', 'CRS-0001', 'TRA-001');

        $this->assertSame('TC001-TRN001-KIT001-CRS0001-TRA001', $code);
    }

    public function test_duplicate_certificate_code_gets_numeric_suffix(): void
    {
        $generator = app(CertificateCodeGenerator::class);
        $base = 'TC001-TRN001-KIT001-CRS0001-TRA001';

        $course = TrainingCourse::query()->with('trainees')->firstOrFail();
        $trainee = $course->trainees()->firstOrFail();

        Certificate::query()->create([
            'trainee_id' => $trainee->id,
            'training_course_id' => $course->id,
            'certificate_number' => 'CERT-DUP-TEST-1',
            'certificate_code' => $base,
            'certificate_type' => 'attendance',
            'result' => 'passed',
            'hours_awarded' => 1,
            'training_hours' => 1,
            'status' => 'draft',
        ]);

        $unique = $generator->ensureUniqueCertificateCode($base);

        $this->assertNotSame($base, $unique);
        $this->assertStringStartsWith($base . '-', $unique);
    }

    public function test_public_verify_by_certificate_code_works_without_login(): void
    {
        $certificate = $this->createApprovedCertificate();

        $this->getJson('/api/verify-certificate/' . $certificate->certificate_code)
            ->assertOk()
            ->assertJsonPath('data.certificate_code', $certificate->certificate_code)
            ->assertJsonPath('data.course_code', $certificate->course_code)
            ->assertJsonMissingPath('data.national_id');
    }

    public function test_public_view_route_shows_certificate_page(): void
    {
        $certificate = $this->createApprovedCertificate();

        app(CertificateService::class)->generateQrForCertificate($certificate->fresh());

        $this->get('/verify-certificate/' . $certificate->certificate_code)
            ->assertOk()
            ->assertSee($certificate->certificate_code, false)
            ->assertSee('شهادة', false);
    }

    public function test_print_by_certificate_code_requires_approval(): void
    {
        $generator = app(CertificateCodeGenerator::class);
        $course = TrainingCourse::query()->with('trainees')->firstOrFail();
        $trainee = $course->trainees()->firstOrFail();

        $certificate = Certificate::query()->create([
            'trainee_id' => $trainee->id,
            'training_course_id' => $course->id,
            'training_center_id' => $course->training_center_id,
            'trainer_id' => $course->trainer_id,
            'training_kit_id' => $course->training_kit_id,
            'training_program_id' => $course->training_program_id,
            'certificate_number' => 'CERT-PENDING-PRINT',
            'certificate_code' => $generator->ensureUniqueCertificateCode('PENDING-PRINT-TEST-CODE'),
            'certificate_type' => 'attendance',
            'result' => 'passed',
            'hours_awarded' => 8,
            'training_hours' => 8,
            'status' => 'pending_center_approval',
            'is_verified' => false,
        ]);

        $this->get('/certificates/' . $certificate->certificate_code . '/print')
            ->assertForbidden();
    }

    public function test_print_by_certificate_code_works_when_approved(): void
    {
        $certificate = $this->createApprovedCertificate();

        $this->get('/certificates/' . $certificate->certificate_code . '/print')
            ->assertOk()
            ->assertSee($certificate->certificate_code, false);
    }

    public function test_print_by_invalid_certificate_code_returns_404(): void
    {
        $this->get('/certificates/INVALID-CODE-XYZ/print')
            ->assertNotFound();
    }

    public function test_unauthorized_user_cannot_issue_certificate(): void
    {
        $traineeUser = User::query()->where('email', 'trainee@system.com')->firstOrFail();
        Sanctum::actingAs($traineeUser);

        $course = TrainingCourse::query()->firstOrFail();
        $traineeId = $course->trainees()->value('trainees.id') ?? 1;

        $this->postJson('/api/certificates/issue', [
            'training_course_id' => $course->id,
            'trainee_id' => $traineeId,
            'certificate_type' => 'attendance',
        ])->assertForbidden();
    }

    public function test_qr_url_points_to_verify_certificate_page(): void
    {
        $certificate = $this->createApprovedCertificate();
        app(CertificateService::class)->generateQrForCertificate($certificate->fresh());

        $certificate->refresh();

        $this->assertStringContainsString(
            '/verify-certificate/' . $certificate->certificate_code,
            (string) $certificate->qr_url
        );
    }

    private function prepareCompletedCourse(User $user): array
    {
        $course = TrainingCourse::query()
            ->when($user->training_center_id, fn ($query) => $query->where('training_center_id', $user->training_center_id))
            ->first();

        if (!$course) {
            $course = TrainingCourse::query()->firstOrFail();
            $user->forceFill(['training_center_id' => $course->training_center_id])->save();
        }

        $course->update(['status' => 'completed', 'actual_hours' => $course->planned_hours ?: 10]);

        $course = $this->ensureCourseEligibleForCertificates($course, $user);

        $trainee = $course->trainees()->firstOrFail();

        return [$course, $trainee];
    }

    private function ensureCourseEligibleForCertificates(TrainingCourse $course, User $user): TrainingCourse
    {
        $course->load(['trainingCenter', 'trainer', 'trainingKit']);

        $center = TrainingCenter::query()->find($user->training_center_id)
            ?? $course->trainingCenter
            ?? TrainingCenter::query()->firstOrFail();

        $center->update([
            'accreditation_status' => 'approved',
            'supports_offline_training' => true,
            'is_active' => true,
            'accreditation_start_date' => now()->subMonths(6)->toDateString(),
            'accreditation_end_date' => now()->addYear()->toDateString(),
        ]);

        $trainer = $course->trainer;
        if (!$trainer || (int) $trainer->training_center_id !== (int) $center->id) {
            $trainer = Trainer::query()
                ->where('training_center_id', $center->id)
                ->first()
                ?? Trainer::query()->firstOrFail();

            $trainer->update(['training_center_id' => $center->id]);
        }

        $trainer->update([
            'can_train' => true,
            'has_tot' => true,
            'status' => 'active',
            'tot_issue_date' => now()->subMonths(6)->toDateString(),
            'tot_expiry_date' => now()->addYear()->toDateString(),
            'accreditation_start_date' => now()->subMonths(3)->toDateString(),
            'accreditation_end_date' => now()->addYear()->toDateString(),
        ]);

        $kitId = $course->training_kit_id ?? TrainingKit::query()->value('id');
        TrainingKit::query()->whereKey($kitId)->update([
            'is_active' => true,
            'status' => 'active',
        ]);

        $trainer->kits()->syncWithoutDetaching([
            $kitId => [
                'is_authorized' => true,
                'authorized_from' => now()->subMonths(2)->toDateString(),
                'authorized_to' => now()->addYear()->toDateString(),
                'notes' => 'test authorization',
            ],
        ]);

        $course->update([
            'training_center_id' => $center->id,
            'trainer_id' => $trainer->id,
            'training_kit_id' => $kitId,
            'delivery_mode' => 'offline',
            'approved_platform' => null,
            'status' => 'completed',
            'actual_hours' => $course->planned_hours ?: 10,
        ]);

        $course = $course->fresh(['trainingCenter', 'trainer', 'trainingKit']);
        $this->assertTrue($course->canIssueCertificates(), 'Course should be eligible for certificates');

        return $course;
    }

    private function createApprovedCertificate(): Certificate
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        [$course, $trainee] = $this->prepareCompletedCourse($user);

        Certificate::query()
            ->where('training_course_id', $course->id)
            ->where('trainee_id', $trainee->id)
            ->delete();

        $course->trainees()->updateExistingPivot($trainee->id, [
            'attendance_status' => 'attended',
            'attended_hours' => 8,
            'result' => 'passed',
            'score' => 90,
        ]);

        $this->postJson('/api/certificates/issue', [
            'training_course_id' => $course->id,
            'trainee_id' => $trainee->id,
            'certificate_type' => CertificateType::ATTENDANCE,
            'hours_awarded' => 8,
        ])->assertCreated();

        $certificate = Certificate::query()
            ->where('training_course_id', $course->id)
            ->where('trainee_id', $trainee->id)
            ->where('certificate_type', 'attendance')
            ->firstOrFail();

        $this->uploadSignaturesForApprovers();

        $approvalService = app(CertificateApprovalService::class);

        foreach ([
            'center_approval',
            'training_manager_approval',
            'deputy_director_approval',
            'general_director_approval',
        ] as $step) {
            $approver = match ($step) {
                'center_approval' => User::query()->where('email', 'center@system.com')->firstOrFail(),
                'training_manager_approval' => User::query()->where('email', 'manager@system.com')->firstOrFail(),
                'deputy_director_approval' => User::query()->where('email', 'deputy@system.com')->firstOrFail(),
                default => User::query()->where('email', 'general@system.com')->firstOrFail(),
            };

            Sanctum::actingAs($approver);

            $approvalService->approve(
                $certificate->fresh(),
                new \App\DTOs\Training\ApproveCertificateData(
                    approvalStep: $step,
                    decision: 'approved',
                    notes: 'test approval',
                ),
                $approver
            );
        }

        return $certificate->fresh();
    }
}
