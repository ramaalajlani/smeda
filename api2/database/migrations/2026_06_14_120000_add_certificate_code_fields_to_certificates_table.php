<?php

use App\Models\Certificate;
use App\Services\Training\CertificateCodeGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('certificate_code', 180)->nullable()->unique()->after('certificate_number');
            $table->string('center_code', 80)->nullable()->after('certificate_type');
            $table->string('trainer_code', 80)->nullable()->after('center_code');
            $table->string('kit_code', 80)->nullable()->after('trainer_code');
            $table->string('course_code', 80)->nullable()->after('kit_code');
            $table->string('trainee_code', 80)->nullable()->after('course_code');
            $table->unsignedInteger('training_hours')->nullable()->after('hours_awarded');
            $table->string('qr_url', 500)->nullable()->after('qr_code_path');
            $table->timestamp('issued_at')->nullable()->after('issue_date');
        });

        $generator = app(CertificateCodeGenerator::class);

        Certificate::query()
            ->with([
                'trainee:id,trainee_code',
                'trainingCenter:id,code',
                'trainer:id,trainer_code',
                'trainingKit:id,code',
                'trainingCourse:id,course_code',
            ])
            ->orderBy('id')
            ->chunkById(100, function ($certificates) use ($generator) {
                foreach ($certificates as $certificate) {
                    $centerCode = $certificate->trainingCenter?->code;
                    $trainerCode = $certificate->trainer?->trainer_code;
                    $kitCode = $certificate->trainingKit?->code;
                    $courseCode = $certificate->trainingCourse?->course_code;
                    $traineeCode = $certificate->trainee?->trainee_code;

                    $baseCode = $generator->buildCertificateCode(
                        $centerCode,
                        $trainerCode,
                        $kitCode,
                        $courseCode,
                        $traineeCode,
                    );

                    $certificateCode = $generator->ensureUniqueCertificateCode($baseCode, (int) $certificate->id);

                    $certificate->forceFill([
                        'certificate_code' => $certificateCode,
                        'center_code' => $centerCode,
                        'trainer_code' => $trainerCode,
                        'kit_code' => $kitCode,
                        'course_code' => $courseCode,
                        'trainee_code' => $traineeCode,
                        'training_hours' => $certificate->training_hours ?? $certificate->hours_awarded,
                        'issued_at' => $certificate->issued_at ?? $certificate->issue_date,
                        'qr_url' => $certificate->qr_url,
                    ])->saveQuietly();
                }
            });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn([
                'certificate_code',
                'center_code',
                'trainer_code',
                'kit_code',
                'course_code',
                'trainee_code',
                'training_hours',
                'qr_url',
                'issued_at',
            ]);
        });
    }
};
