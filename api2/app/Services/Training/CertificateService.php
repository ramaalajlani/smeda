<?php

namespace App\Services\Training;

use App\DTOs\Training\ApproveCertificateData;
use App\DTOs\Training\IssueCertificateData;
use App\Models\Certificate;
use App\Models\CertificateApproval;
use App\Models\Trainee;
use App\Models\TrainingCourse;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\CertificatePublicPayload;
use App\Support\CertificateType;
use App\Support\TrainingDataScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificateService
{
    public function __construct(
        private EntityCodeGenerator $codeGenerator,
        private CertificateCodeGenerator $certificateCodeGenerator,
        private AuditLogService $auditLog,
    ) {}

    public function issueCertificate(IssueCertificateData $data, User $user): Certificate
    {
        $certificateType = CertificateType::normalize($data->certificateType);

        $course = TrainingCourse::query()
            ->with(['trainingCenter.platforms', 'trainer.kits', 'trainingKit', 'trainingProgram'])
            ->findOrFail($data->trainingCourseId);

        $trainee = Trainee::query()->findOrFail($data->traineeId);

        if (!$course->canIssueCertificates()) {
            throw ValidationException::withMessages(['training_course_id' => ['الدورة غير مؤهلة لإصدار الشهادات.']]);
        }

        if (!TrainingDataScope::canAccessTrainingCourse($user, $course->training_center_id, $course->trainer_id)) {
            throw ValidationException::withMessages(['training_course_id' => ['لا يمكنك إصدار شهادة لدورة خارج نطاق صلاحياتك.']]);
        }

        if (!$course->hasTrainee($data->traineeId)) {
            throw ValidationException::withMessages(['trainee_id' => ['المتدرب غير مسجل في هذه الدورة.']]);
        }

        if ($course->hasCertificateForTrainee($data->traineeId, $certificateType)) {
            throw ValidationException::withMessages(['certificate_type' => ['تم إصدار شهادة من نفس النوع لهذا المتدرب مسبقاً ضمن هذه الدورة.']]);
        }

        $pivot = $course->trainees()->where('trainees.id', $data->traineeId)->first()?->pivot;

        if (!$pivot) {
            throw ValidationException::withMessages(['trainee_id' => ['بيانات تسجيل المتدرب في الدورة غير متوفرة.']]);
        }

        if ($message = $this->canIssueAttendance($certificateType, $pivot)) {
            throw ValidationException::withMessages(['certificate_type' => [$message]]);
        }

        if ($message = $this->canIssueCompletion($certificateType, $pivot, $data->score)) {
            throw ValidationException::withMessages(['certificate_type' => [$message]]);
        }

        if (CertificateType::isCompletion($certificateType) && empty($data->result) && $pivot->result !== 'passed') {
            throw ValidationException::withMessages(['result' => ['يجب تحديد نتيجة لشهادة الاجتياز.']]);
        }

        $resolvedResult = $data->result
            ?? (CertificateType::isAttendance($certificateType)
                ? 'passed'
                : ($pivot->result === 'passed' ? 'passed' : 'pending'));

        $resolvedHours = $data->hoursAwarded
            ?? (CertificateType::isAttendance($certificateType)
                ? (int) $pivot->attended_hours
                : $course->actual_hours_resolved);

        $resolvedScore = $data->score ?? $pivot->score;

        $centerCode = $course->trainingCenter?->code;
        $trainerCode = $course->trainer?->trainer_code;
        $kitCode = $course->trainingKit?->code;
        $courseCode = $course->course_code;
        $traineeCode = $trainee->trainee_code;

        if (blank($centerCode)) {
            throw ValidationException::withMessages(['training_course_id' => ['رمز المركز التدريبي غير متوفر.']]);
        }

        if (blank($trainerCode)) {
            throw ValidationException::withMessages(['training_course_id' => ['رمز المدرب غير متوفر.']]);
        }

        if (blank($kitCode)) {
            throw ValidationException::withMessages(['training_course_id' => ['رمز الحقيبة التدريبية غير متوفر.']]);
        }

        if (blank($courseCode)) {
            throw ValidationException::withMessages(['training_course_id' => ['رمز الدورة غير متوفر.']]);
        }

        if (blank($traineeCode)) {
            throw ValidationException::withMessages(['trainee_id' => ['رمز المتدرب غير متوفر.']]);
        }

        $certificate = DB::transaction(function () use (
            $data,
            $course,
            $trainee,
            $certificateType,
            $resolvedResult,
            $resolvedHours,
            $resolvedScore,
            $user,
            $centerCode,
            $trainerCode,
            $kitCode,
            $courseCode,
            $traineeCode,
        ) {
            $nextId = (Certificate::query()->lockForUpdate()->max('id') ?? 0) + 1;
            $certificateNumber = 'CERT-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);

            $baseCertificateCode = $this->certificateCodeGenerator->buildCertificateCode(
                $centerCode,
                $trainerCode,
                $kitCode,
                $courseCode,
                $traineeCode,
            );

            $certificateCode = $this->certificateCodeGenerator->ensureUniqueCertificateCode($baseCertificateCode);

            $issuedAt = now();

            $certificate = Certificate::create([
                'trainee_id' => $data->traineeId,
                'training_center_id' => $course->training_center_id,
                'trainer_id' => $course->trainer_id,
                'training_kit_id' => $course->training_kit_id,
                'training_program_id' => $course->training_program_id,
                'training_course_id' => $course->id,
                'branch_id' => $course->branch_id,
                'governorate_id' => $course->governorate_id,
                'certificate_number' => $certificateNumber,
                'certificate_code' => $certificateCode,
                'reference_number' => $this->codeGenerator->nextReferenceNumber($nextId),
                'verification_code' => $this->codeGenerator->nextVerificationCode(),
                'certificate_type' => $certificateType,
                'center_code' => $centerCode,
                'trainer_code' => $trainerCode,
                'kit_code' => $kitCode,
                'course_code' => $courseCode,
                'trainee_code' => $traineeCode,
                'result' => $resolvedResult,
                'score' => $resolvedScore,
                'hours_awarded' => $resolvedHours,
                'training_hours' => $resolvedHours,
                'status' => 'pending_center_approval',
                'issue_date' => $issuedAt->toDateString(),
                'issued_at' => $issuedAt,
                'certificate_date' => $issuedAt->toDateString(),
                'is_verified' => false,
                'verified_at' => null,
                'qr_code_path' => null,
                'qr_url' => null,
                'notes' => $this->appendIssuerNote($data->notes, $user->id),
            ]);

            foreach ([
                'center_approval',
                'training_manager_approval',
                'deputy_director_approval',
                'general_director_approval',
            ] as $step) {
                CertificateApproval::create([
                    'certificate_id' => $certificate->id,
                    'approved_by' => null,
                    'approval_step' => $step,
                    'decision' => 'pending',
                    'decision_at' => null,
                    'notes' => null,
                ]);
            }

            $this->auditLog->log('certificate_issued', $user, $certificate, null, [
                'certificate_number' => $certificate->certificate_number,
                'certificate_code' => $certificate->certificate_code,
                'certificate_type' => $certificate->certificate_type,
                'training_course_id' => $course->id,
                'trainee_id' => $data->traineeId,
            ]);

            return $certificate;
        });

        return $certificate->load($this->defaultRelations());
    }

    public function findByCode(string $certificateCode): ?Certificate
    {
        return Certificate::query()
            ->where('certificate_code', $certificateCode)
            ->first();
    }

    public function canIssueAttendance(string $certificateType, $pivot): ?string
    {
        if (!CertificateType::isAttendance($certificateType)) {
            return null;
        }

        if (!in_array($pivot->attendance_status, ['attended', 'completed'], true)) {
            return 'حالة حضور المتدرب لا تؤهل لإصدار شهادة حضور.';
        }

        if ((int) $pivot->attended_hours <= 0) {
            return 'يجب تسجيل ساعات حضور للمتدرب قبل إصدار شهادة الحضور.';
        }

        return null;
    }

    public function canIssueCompletion(string $certificateType, $pivot, ?float $score): ?string
    {
        if (!CertificateType::isCompletion($certificateType)) {
            return null;
        }

        if ($pivot->result !== 'passed') {
            return 'نتيجة المتدرب لا تؤهل لإصدار شهادة اجتياز تدريب.';
        }

        $resolvedScore = $score ?? $pivot->score;
        if ($resolvedScore === null) {
            return 'يجب تحديد درجة المتدرب لإصدار شهادة الاجتياز.';
        }

        return null;
    }

    public function buildAntiFakeHash(Certificate $certificate): string
    {
        $payload = implode('|', [
            (string) $certificate->id,
            (string) $certificate->certificate_number,
            (string) $certificate->certificate_code,
            (string) $certificate->reference_number,
            (string) $certificate->verification_code,
            (string) $certificate->trainee_id,
            (string) $certificate->training_course_id,
            (string) $certificate->certificate_type,
            (string) $certificate->resolvedTrainingHours(),
            (string) optional($certificate->issue_date)->format('Y-m-d'),
            (string) $certificate->status,
        ]);

        return hash_hmac('sha256', $payload, (string) config('app.key'));
    }

    public function safeVerifyPayload(Certificate $certificate, bool $approved): array
    {
        return $approved
            ? CertificatePublicPayload::forVerification($certificate)
            : CertificatePublicPayload::forPendingVerification($certificate);
    }

    public function generateQrForCertificate(Certificate $certificate): void
    {
        if (blank($certificate->certificate_code)) {
            return;
        }

        $relativeDir = 'certificates/qr';
        $storageDir = storage_path('app/public/' . $relativeDir);

        if (!File::exists($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        $viewUrl = $this->certificateCodeGenerator->publicViewUrl($certificate->certificate_code);
        $fileName = 'certificate-' . $certificate->id . '.svg';
        $relativePath = $relativeDir . '/' . $fileName;
        $fullPath = $storageDir . '/' . $fileName;

        $qrSvg = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($viewUrl);

        file_put_contents($fullPath, $qrSvg);

        $certificate->update([
            'qr_code_path' => 'storage/' . $relativePath,
            'qr_url' => $viewUrl,
        ]);
    }

    public function defaultRelations(): array
    {
        return [
            'trainee:id,name,trainee_code,national_id,status',
            'trainingCenter:id,name,code,city',
            'trainer:id,name,trainer_code',
            'trainingKit:id,name,code,level,hours',
            'trainingProgram:id,name,code',
            'trainingCourse:id,course_code,title,delivery_mode,approved_platform,start_date,end_date',
            'approvals:id,certificate_id,approved_by,approval_step,decision,decision_at,notes',
            'approvals.electronicSignature:id,signable_type,signable_id,role_key,signer_name,signer_title,verification_code,signed_at,signature_image_path,signature_image_hash',
            'approvals.approver:id,name',
        ];
    }

    private function appendIssuerNote(?string $notes, int $issuerId): ?string
    {
        $issuerLine = 'issued_by_user_id=' . $issuerId;

        return $notes ? trim($notes . "\n" . $issuerLine) : $issuerLine;
    }
}
