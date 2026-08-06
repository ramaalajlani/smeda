<?php

namespace App\DTOs\Training;

use App\Http\Requests\Training\UpdateCourseTraineeResultRequest;

readonly class UpdateCourseTraineeResultData
{
    public function __construct(
        public ?string $attendanceStatus = null,
        public ?string $result = null,
        public ?float $score = null,
        public ?int $attendedHours = null,
        public ?string $notes = null,
    ) {}

    public static function fromRequest(UpdateCourseTraineeResultRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            attendanceStatus: $validated['attendance_status'] ?? null,
            result: $validated['result'] ?? null,
            score: array_key_exists('score', $validated) ? ($validated['score'] !== null ? (float) $validated['score'] : null) : null,
            attendedHours: isset($validated['attended_hours']) ? (int) $validated['attended_hours'] : null,
            notes: $validated['notes'] ?? null,
        );
    }

    public function toPivotArray(): array
    {
        return array_filter([
            'attendance_status' => $this->attendanceStatus,
            'result' => $this->result,
            'score' => $this->score,
            'attended_hours' => $this->attendedHours,
            'notes' => $this->notes,
        ], fn ($v) => $v !== null);
    }
}
