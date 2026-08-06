<?php

namespace App\DTOs\Training;

use App\Http\Requests\Training\UpdateTrainingCourseRequest;

readonly class UpdateTrainingCourseData
{
    public function __construct(
        public array $fields,
        public array $locationFields,
    ) {}

    public static function fromRequest(UpdateTrainingCourseRequest $request): self
    {
        $validated = $request->validated();
        $locationKeys = [
            'venue_name', 'governorate', 'city', 'district', 'address',
            'latitude', 'longitude', 'location_visibility', 'online_platform', 'online_url',
        ];

        $locationFields = [];
        $fields = [];

        foreach ($validated as $key => $value) {
            if (in_array($key, $locationKeys, true)) {
                $locationFields[$key] = $value;
            } else {
                $fields[$key] = $value;
            }
        }

        return new self($fields, $locationFields);
    }
}
