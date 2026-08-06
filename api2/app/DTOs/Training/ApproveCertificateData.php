<?php

namespace App\DTOs\Training;

use App\Http\Requests\Training\ApproveCertificateRequest;

readonly class ApproveCertificateData
{
    public function __construct(
        public string $approvalStep,
        public string $decision,
        public ?string $notes,
    ) {}

    public static function fromRequest(ApproveCertificateRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            approvalStep: $validated['approval_step'],
            decision: $validated['decision'],
            notes: $validated['notes'] ?? null,
        );
    }
}
