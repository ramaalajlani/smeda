<?php

namespace App\Support;

use App\Models\Certificate;

class CertificatePublicPayload
{
    public static function forVerification(Certificate $certificate): array
    {
        $payload = self::basePayload($certificate);

        if (CertificateType::isCompletion($certificate->certificate_type)) {
            $payload['score'] = $certificate->score;
        }

        return $payload;
    }

    public static function forPendingVerification(Certificate $certificate): array
    {
        return [
            'certificate_number' => $certificate->certificate_number,
            'certificate_code' => $certificate->certificate_code,
            'status' => $certificate->status,
            'is_verified' => (bool) $certificate->is_verified,
        ];
    }

    public static function forVerifyPage(Certificate $certificate, bool $isApproved): array
    {
        $base = self::basePayload($certificate);
        $base['is_approved'] = $isApproved;
        $base['print_url'] = $isApproved ? $certificate->publicPrintUrl() : null;

        if (CertificateType::isCompletion($certificate->certificate_type)) {
            $base['score'] = $certificate->score;
        }

        return $base;
    }

    private static function basePayload(Certificate $certificate): array
    {
        return [
            'certificate_number' => $certificate->certificate_number,
            'certificate_code' => $certificate->certificate_code,
            'certificate_type' => $certificate->certificate_type,
            'certificate_type_label' => CertificateType::label($certificate->certificate_type),
            'trainee_name' => $certificate->trainee?->name,
            'course_title' => $certificate->trainingCourse?->title ?? $certificate->trainingKit?->name,
            'course_code' => $certificate->course_code ?? $certificate->trainingCourse?->course_code,
            'kit_name' => $certificate->trainingKit?->name,
            'center_name' => $certificate->trainingCenter?->name,
            'trainer_name' => $certificate->trainer?->name,
            'center_code' => $certificate->center_code ?? $certificate->trainingCenter?->code,
            'trainer_code' => $certificate->trainer_code ?? $certificate->trainer?->trainer_code,
            'kit_code' => $certificate->kit_code ?? $certificate->trainingKit?->code,
            'trainee_code' => $certificate->trainee_code ?? $certificate->trainee?->trainee_code,
            'training_hours' => $certificate->resolvedTrainingHours(),
            'hours_awarded' => $certificate->resolvedTrainingHours(),
            'status' => $certificate->status,
            'issue_date' => optional($certificate->issue_date)->format('Y-m-d'),
            'issued_at' => optional($certificate->issued_at ?? $certificate->issue_date)->format('Y-m-d'),
            'approved_at' => optional($certificate->verified_at)->format('Y-m-d H:i:s'),
            'result' => $certificate->result,
            'view_url' => $certificate->publicViewUrl(),
        ];
    }
}
