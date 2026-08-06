<?php

namespace App\DTOs\Training;

use App\Http\Requests\Training\StoreTrainingCourseRequest;
use App\Http\Requests\Training\UpdateTrainingCourseRequest;

readonly class StoreTrainingCourseData
{
    public function __construct(
        public int $trainingCenterId,
        public int $trainerId,
        public int $trainingKitId,
        public ?int $trainingProgramId,
        public string $title,
        public string $deliveryMode,
        public ?string $approvedPlatform,
        public ?string $startDate,
        public ?string $endDate,
        public int $plannedHours,
        public int $actualHours,
        public int $capacity,
        public string $status,
        public ?string $notes,
        public array $locationFields,
    ) {}

    public static function fromRequest(StoreTrainingCourseRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            trainingCenterId: (int) $validated['training_center_id'],
            trainerId: (int) $validated['trainer_id'],
            trainingKitId: (int) $validated['training_kit_id'],
            trainingProgramId: isset($validated['training_program_id']) ? (int) $validated['training_program_id'] : null,
            title: $validated['title'],
            deliveryMode: $validated['delivery_mode'],
            approvedPlatform: $validated['approved_platform'] ?? null,
            startDate: $validated['start_date'] ?? null,
            endDate: $validated['end_date'] ?? null,
            plannedHours: (int) $validated['planned_hours'],
            actualHours: (int) ($validated['actual_hours'] ?? 0),
            capacity: (int) $validated['capacity'],
            status: $validated['status'] ?? 'draft',
            notes: $validated['notes'] ?? null,
            locationFields: LocationData::extractFromArray($validated),
        );
    }
}
