<?php

namespace App\Services\Training;

use App\Models\TrainingCenter;
use App\Support\TrainingLocationFormatter;

class TrainingLocationService
{
    public function resolveCourseLocationData(array $validated, TrainingCenter $center): array
    {
        if (($validated['delivery_mode'] ?? null) === 'online') {
            return [
                'venue_name' => null,
                'governorate' => null,
                'city' => null,
                'district' => null,
                'address' => null,
                'latitude' => null,
                'longitude' => null,
                'location_visibility' => $validated['location_visibility'] ?? 'internal',
                'online_platform' => $validated['online_platform'] ?? $validated['approved_platform'] ?? null,
                'online_url' => $validated['online_url'] ?? null,
            ];
        }

        return $this->copyCenterLocationToOfflineCourse($validated, $center);
    }

    public function copyCenterLocationToOfflineCourse(array $validated, TrainingCenter $center): array
    {
        return [
            'venue_name' => $validated['venue_name'] ?? null,
            'governorate' => $validated['governorate'] ?? $center->governorate,
            'city' => $validated['city'] ?? $center->city,
            'district' => $validated['district'] ?? $center->district,
            'address' => $validated['address'] ?? $center->address,
            'latitude' => $validated['latitude'] ?? $center->latitude,
            'longitude' => $validated['longitude'] ?? $center->longitude,
            'location_visibility' => $validated['location_visibility'] ?? 'public',
            'online_platform' => null,
            'online_url' => null,
        ];
    }

    public function validateOfflineCourseLocation(array $validated, TrainingCenter $center): ?string
    {
        if (($validated['delivery_mode'] ?? null) !== 'offline') {
            return null;
        }

        $location = $this->resolveCourseLocationData($validated, $center);
        $hasCoords = $location['latitude'] !== null && $location['longitude'] !== null;
        $hasCityOrAddress = !empty($location['city']) || !empty($location['address']);

        if (!$hasCoords && !$hasCityOrAddress) {
            return 'يجب تحديد موقع الدورة الحضورية (مدينة/عنوان أو إحداثيات) أو ربطها بمركز يحتوي موقعاً.';
        }

        return null;
    }

    public function formatLocation(string $type, $model, $user): ?array
    {
        return match ($type) {
            'training_center' => TrainingLocationFormatter::forCenter($model, $user),
            'training_course' => TrainingLocationFormatter::forCourse($model, $user),
            'trainer' => TrainingLocationFormatter::forTrainer($model, $user),
            'trainee' => TrainingLocationFormatter::forTrainee($model, $user),
            default => null,
        };
    }

    public function locationValidationRules(bool $partial = false): array
    {
        $prefix = $partial ? 'sometimes' : 'nullable';

        return [
            'venue_name' => [$prefix, 'string', 'max:255'],
            'governorate' => [$prefix, 'string', 'max:100'],
            'city' => [$prefix, 'string', 'max:100'],
            'district' => [$prefix, 'string', 'max:100'],
            'address' => [$prefix, 'string', 'max:1000'],
            'latitude' => [$prefix, 'numeric', 'between:-90,90'],
            'longitude' => [$prefix, 'numeric', 'between:-180,180'],
            'location_visibility' => [$prefix, 'in:public,internal,private'],
            'online_platform' => [$prefix, 'string', 'max:150'],
            'online_url' => [$prefix, 'url', 'max:500'],
        ];
    }
}
