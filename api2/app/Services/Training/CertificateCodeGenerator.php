<?php

namespace App\Services\Training;

use App\Models\Certificate;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\TrainingKit;

class CertificateCodeGenerator
{
    public function buildCertificateCode(
        ?string $centerCode,
        ?string $trainerCode,
        ?string $kitCode,
        ?string $courseCode,
        ?string $traineeCode,
    ): string {
        return implode('-', [
            $this->normalizeSegment($centerCode, 'CTR'),
            $this->normalizeSegment($trainerCode, 'TR'),
            $this->normalizeSegment($kitCode, 'KIT'),
            $this->normalizeSegment($courseCode, 'CRS'),
            $this->normalizeSegment($traineeCode, 'STU'),
        ]);
    }

    public function buildFromRelations(
        ?TrainingCenter $center,
        ?Trainer $trainer,
        ?TrainingKit $kit,
        ?TrainingCourse $course,
        ?Trainee $trainee,
    ): string {
        return $this->buildCertificateCode(
            $center?->code,
            $trainer?->trainer_code,
            $kit?->code,
            $course?->course_code,
            $trainee?->trainee_code,
        );
    }

    public function ensureUniqueCertificateCode(string $baseCode, ?int $ignoreId = null): string
    {
        $code = $baseCode;
        $suffix = 1;

        while ($this->codeExists($code, $ignoreId)) {
            $code = $baseCode . '-' . str_pad((string) $suffix, 3, '0', STR_PAD_LEFT);
            $suffix++;
        }

        return $code;
    }

    public function publicViewUrl(string $certificateCode): string
    {
        return url('/verify-certificate/' . rawurlencode($certificateCode));
    }

    public function publicPrintUrl(string $certificateCode): string
    {
        return url('/certificates/' . rawurlencode($certificateCode) . '/print');
    }

    private function codeExists(string $code, ?int $ignoreId = null): bool
    {
        return Certificate::withTrashed()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('certificate_code', $code)
            ->exists();
    }

    private function normalizeSegment(?string $code, string $fallbackPrefix): string
    {
        if (blank($code)) {
            return $fallbackPrefix . '00';
        }

        $normalized = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $code));

        return $normalized !== '' ? $normalized : $fallbackPrefix . '00';
    }
}
