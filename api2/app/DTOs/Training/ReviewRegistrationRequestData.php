<?php

namespace App\DTOs\Training;

use App\Http\Requests\Training\ReviewRegistrationRequest;

readonly class ReviewRegistrationRequestData
{
    public function __construct(
        public string $status,
        public ?string $notes,
    ) {}

    public static function fromRequest(ReviewRegistrationRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            status: $validated['status'],
            notes: $validated['decision_notes'] ?? $validated['review_notes'] ?? null,
        );
    }
}
