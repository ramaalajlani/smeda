<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Certificate;
use App\Models\CertificateApproval;
use App\Services\Training\CertificateCodeGenerator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $rows = DB::table('training_course_trainee')
            ->join('training_courses', 'training_courses.id', '=', 'training_course_trainee.training_course_id')
            ->select([
                'training_course_trainee.trainee_id',
                'training_course_trainee.training_course_id',
                'training_course_trainee.result as pivot_result',
                'training_course_trainee.score as pivot_score',
                'training_course_trainee.attended_hours',
                'training_course_trainee.attendance_status',
                'training_courses.training_center_id',
                'training_courses.trainer_id',
                'training_courses.training_kit_id',
                'training_courses.training_program_id',
                'training_courses.actual_hours',
                'training_courses.planned_hours',
            ])
            ->limit(30)
            ->get();

        if ($rows->isEmpty()) {
            $this->command?->warn('No course-trainee rows found. CertificateSeeder skipped.');
            return;
        }

        foreach ($rows as $index => $row) {
            $certificateNumber = 'CERT-' . str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT);

            $certificateType = $row->pivot_result === 'passed' ? 'pass' : 'attendance';
            $certificateResult = $certificateType === 'pass'
                ? 'passed'
                : ($row->attendance_status === 'attended' ? 'passed' : 'failed');

            $hours = (int) ($row->attended_hours ?: ($row->actual_hours ?: $row->planned_hours ?: 0));

            $certificate = Certificate::updateOrCreate(
                ['certificate_number' => $certificateNumber],
                [
                    'trainee_id' => $row->trainee_id,
                    'training_center_id' => $row->training_center_id,
                    'trainer_id' => $row->trainer_id,
                    'training_kit_id' => $row->training_kit_id,
                    'training_program_id' => $row->training_program_id,
                    'training_course_id' => $row->training_course_id,
                    'reference_number' => 'REF-' . now()->format('Ymd') . '-' . str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                    'verification_code' => strtoupper(Str::random(12)),
                    'certificate_type' => $certificateType,
                    'result' => $certificateResult,
                    'score' => $row->pivot_score,
                    'hours_awarded' => $hours,
                    'training_hours' => $hours,
                    'status' => 'approved',
                    'issue_date' => now()->toDateString(),
                    'issued_at' => now(),
                    'certificate_date' => now()->toDateString(),
                    'is_verified' => true,
                    'verified_at' => now(),
                    'notes' => 'Seeded certificate',
                ]
            );

            $certificate->load([
                'trainee:id,trainee_code',
                'trainingCenter:id,code',
                'trainer:id,trainer_code',
                'trainingKit:id,code',
                'trainingCourse:id,course_code',
            ]);

            $generator = app(CertificateCodeGenerator::class);
            $baseCode = $generator->buildFromRelations(
                $certificate->trainingCenter,
                $certificate->trainer,
                $certificate->trainingKit,
                $certificate->trainingCourse,
                $certificate->trainee,
            );

            $certificate->update([
                'certificate_code' => $generator->ensureUniqueCertificateCode($baseCode, (int) $certificate->id),
                'center_code' => $certificate->trainingCenter?->code,
                'trainer_code' => $certificate->trainer?->trainer_code,
                'kit_code' => $certificate->trainingKit?->code,
                'course_code' => $certificate->trainingCourse?->course_code,
                'trainee_code' => $certificate->trainee?->trainee_code,
            ]);

            foreach ([
                'center_approval',
                'training_manager_approval',
                'deputy_director_approval',
            ] as $step) {
                CertificateApproval::updateOrCreate(
                    [
                        'certificate_id' => $certificate->id,
                        'approval_step' => $step,
                    ],
                    [
                        'approved_by' => 1,
                        'decision' => 'approved',
                        'decision_at' => now(),
                        'notes' => 'Approved by seeder',
                    ]
                );
            }

            $this->generateQrForCertificate($certificate->fresh());
        }
    }

    private function buildAntiFakeHash(Certificate $certificate): string
    {
        $payload = implode('|', [
            (string) $certificate->id,
            (string) $certificate->certificate_number,
            (string) $certificate->reference_number,
            (string) $certificate->verification_code,
            (string) $certificate->trainee_id,
            (string) $certificate->training_course_id,
            (string) $certificate->certificate_type,
            (string) $certificate->hours_awarded,
            (string) optional($certificate->issue_date)->format('Y-m-d'),
            (string) $certificate->status,
        ]);

        return hash_hmac('sha256', $payload, (string) config('app.key'));
    }

    private function generateQrForCertificate(Certificate $certificate): void
    {
        $relativeDir = 'certificates/qr';
        $storageDir = storage_path('app/public/' . $relativeDir);

        if (!File::exists($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        $hash = $this->buildAntiFakeHash($certificate);

        $verifyUrl = $certificate->certificate_code
            ? url('/verify-certificate/' . rawurlencode($certificate->certificate_code))
            : url('/certificates/verify?code=' . urlencode((string) $certificate->verification_code) . '&hash=' . urlencode($hash));

        $fileName = 'certificate-' . $certificate->id . '.svg';
        $relativePath = $relativeDir . '/' . $fileName;
        $fullPath = $storageDir . '/' . $fileName;

        $qrSvg = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($verifyUrl);

        file_put_contents($fullPath, $qrSvg);

        $certificate->update([
            'qr_code_path' => 'storage/' . $relativePath,
            'qr_url' => $verifyUrl,
        ]);
    }
}