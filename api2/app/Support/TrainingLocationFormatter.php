<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class TrainingLocationFormatter
{
    public static function canViewInternal(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return TrainingDataScope::hasBroadTrainingReadAccess($user)
            || $user->isCenterUser()
            || $user->isTrainerUser();
    }

    public static function canViewCenterLicense(?User $user, ?int $centerId): bool
    {
        if (!$user) {
            return false;
        }

        if (TrainingDataScope::hasUnrestrictedTrainingAccess($user)) {
            return true;
        }

        return $user->isCenterUser() && $user->belongsToCenter($centerId);
    }

    public static function forCenter(Model $center, ?User $user, bool $includeLicense = true): ?array
    {
        $visibility = $center->location_visibility ?? 'public';

        if ($visibility === 'private') {
            return null;
        }

        if ($visibility === 'internal' && !self::canViewInternal($user)) {
            return self::publicSummary($center, false);
        }

        $location = [
            'governorate' => $center->governorate,
            'city' => $center->city,
            'district' => $center->district,
            'address' => $center->address,
            'latitude' => $center->latitude !== null ? (float) $center->latitude : null,
            'longitude' => $center->longitude !== null ? (float) $center->longitude : null,
            'visibility' => $visibility,
        ];

        if ($includeLicense && self::canViewCenterLicense($user, $center->id)) {
            $location['license'] = [
                'number' => $center->license_number,
                'issue_date' => optional($center->license_issue_date)?->format('Y-m-d'),
                'expiry_date' => optional($center->license_expiry_date)?->format('Y-m-d'),
                'issued_by' => $center->license_issued_by,
                'image_path' => $center->license_image_path,
            ];
        }

        return $location;
    }

    public static function forTrainer(Model $trainer, ?User $user): ?array
    {
        $visibility = $trainer->location_visibility ?? 'internal';

        if ($visibility === 'private') {
            return null;
        }

        if (!self::canViewInternal($user)) {
            return null;
        }

        if (!TrainingDataScope::canViewTrainerContact($user, $trainer->id, $trainer->training_center_id)) {
            return self::publicSummary($trainer, false);
        }

        return [
            'governorate' => $trainer->governorate,
            'city' => $trainer->city,
            'district' => $trainer->district,
            'service_areas' => $trainer->service_areas,
            'visibility' => $visibility,
        ];
    }

    public static function forTrainee(Model $trainee, ?User $user): ?array
    {
        $visibility = $trainee->location_visibility ?? 'private';

        if (!TrainingDataScope::canViewTraineeSensitive($user, $trainee->id)) {
            return null;
        }

        return [
            'governorate' => $trainee->governorate,
            'city' => $trainee->city,
            'district' => $trainee->district,
            'address' => $visibility === 'private' && !$user?->isTraineeUser()
                ? null
                : $trainee->address,
            'visibility' => $visibility,
        ];
    }

    public static function forCourse(Model $course, ?User $user): ?array
    {
        if ($course->delivery_mode === 'online') {
            $canSeeInternal = self::canViewInternal($user);

            return [
                'governorate' => null,
                'city' => null,
                'district' => null,
                'address' => null,
                'latitude' => null,
                'longitude' => null,
                'visibility' => $course->location_visibility ?? 'internal',
                'venue_name' => null,
                'online_platform' => $course->online_platform ?? $course->approved_platform,
                'online_url' => $canSeeInternal ? $course->online_url : null,
            ];
        }

        $visibility = $course->location_visibility ?? 'public';

        if ($visibility === 'private') {
            return null;
        }

        if ($visibility === 'internal' && !self::canViewInternal($user)) {
            return self::publicSummary($course, true);
        }

        return [
            'governorate' => $course->governorate,
            'city' => $course->city,
            'district' => $course->district,
            'address' => $course->address,
            'latitude' => $course->latitude !== null ? (float) $course->latitude : null,
            'longitude' => $course->longitude !== null ? (float) $course->longitude : null,
            'visibility' => $visibility,
            'venue_name' => $course->venue_name,
            'online_platform' => null,
            'online_url' => null,
        ];
    }

    public static function mapPoint(
        string $type,
        Model $model,
        ?User $user,
        string $linkRoute,
        array $linkParams = []
    ): ?array {
        $location = match ($type) {
            'training_center' => self::forCenter($model, $user, false),
            'training_course' => self::forCourse($model, $user),
            'trainer' => self::forTrainer($model, $user),
            default => null,
        };

        if ($location === null) {
            return null;
        }

        $name = match ($type) {
            'training_center' => $model->name,
            'training_course' => $model->title,
            'trainer' => $model->name,
            default => null,
        };

        $status = match ($type) {
            'training_center' => $model->accreditation_status,
            'training_course' => $model->status,
            'trainer' => $model->status,
            default => null,
        };

        return [
            'id' => $model->id,
            'type' => $type,
            'name' => $name,
            'governorate' => $location['governorate'] ?? null,
            'city' => $location['city'] ?? null,
            'address' => $location['address'] ?? null,
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            'status' => $status,
            'link' => self::resolveMapLink($linkRoute, $linkParams),
        ];
    }

    private static function resolveMapLink(string $linkRoute, array $linkParams): ?string
    {
        if (!Route::has($linkRoute)) {
            return null;
        }

        $id = isset($linkParams['id']) ? (int) $linkParams['id'] : null;

        if ($id !== null) {
            $signed = match ($linkRoute) {
                'training-centers.certificate' => SignedPrintUrl::trainingCenterCertificate($id),
                'trainers.card' => SignedPrintUrl::trainerCard($id),
                default => null,
            };

            if ($signed !== null) {
                return $signed;
            }
        }

        return route($linkRoute, $linkParams);
    }

    private static function publicSummary(Model $model, bool $includeAddress): array
    {
        return array_filter([
            'governorate' => $model->governorate ?? null,
            'city' => $model->city ?? null,
            'district' => null,
            'address' => $includeAddress ? ($model->address ?? null) : null,
            'latitude' => null,
            'longitude' => null,
            'visibility' => $model->location_visibility ?? 'public',
        ], fn ($value) => $value !== null);
    }
}
