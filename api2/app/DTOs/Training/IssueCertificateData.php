<?php

namespace App\DTOs\Training;

use App\Http\Requests\Training\IssueCertificateRequest;
use App\Support\CertificateType;

readonly class IssueCertificateData
{
    public function __construct(
        public int $trainingCourseId,
        public int $traineeId,
        public string $certificateType,
        public ?string $result,
        public ?float $score,
        public ?int $hoursAwarded,
        public ?string $notes,
    ) {}

    public static function fromRequest(IssueCertificateRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            trainingCourseId: (int) $validated['training_course_id'],
            traineeId: (int) $validated['trainee_id'],
            certificateType: CertificateType::normalize($validated['certificate_type']),
            result: $validated['result'] ?? null,
            score: isset($validated['score']) ? (float) $validated['score'] : null,
            hoursAwarded: isset($validated['hours_awarded']) ? (int) $validated['hours_awarded'] : null,
            notes: $validated['notes'] ?? null,
        );
    }
}
