<?php

namespace App\Http\Resources;

use App\Models\Trainer;
use App\Support\TrainingDataScope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerProfileResource extends JsonResource
{
    public static function emptyForTrainer(Trainer $trainer): array
    {
        $trainer->loadMissing('trainingCenter:id,name,code,city');

        return [
            'id' => null,
            'trainer_id' => $trainer->id,
            'headline' => null,
            'bio' => null,
            'experience_years' => null,
            'skills' => null,
            'special_interests' => null,
            'linkedin_summary' => null,
            'cv_file' => null,
            'profile_image' => null,
            'visibility' => null,
            'cv_file_url' => null,
            'profile_image_url' => null,
            'trainer' => self::trainerPayload($trainer, request(), false),
            'created_at' => null,
            'updated_at' => null,
        ];
    }

    public function toArray(Request $request): array
    {
        $user = $request->user();
        $trainer = $this->trainer;
        $canSeeContact = $trainer && TrainingDataScope::canViewTrainerContact(
            $user,
            $trainer->id,
            $trainer->training_center_id
        );
        $canSeeFiles = $canSeeContact || (
            $user?->trainer_id && (int) $user->trainer_id === (int) $this->trainer_id
        );

        return [
            'id' => $this->id,
            'trainer_id' => $this->trainer_id,

            'headline' => $this->headline,
            'bio' => $this->bio,
            'experience_years' => $this->experience_years,
            'skills' => $this->skills,
            'special_interests' => $this->special_interests,
            'linkedin_summary' => $this->linkedin_summary,

            'cv_file' => $this->when($canSeeFiles, $this->cv_file),
            'profile_image' => $this->when($canSeeFiles, $this->profile_image),
            'visibility' => $this->visibility,

            'cv_file_url' => $this->when($canSeeFiles && $this->cv_file, url($this->cv_file)),
            'profile_image_url' => $this->when($canSeeFiles && $this->profile_image, url($this->profile_image)),

            'trainer' => $this->whenLoaded('trainer', fn () => self::trainerPayload(
                $this->trainer,
                $request,
                $canSeeContact
            )),

            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }

    private static function trainerPayload(Trainer $trainer, Request $request, bool $canSeeContact): array
    {
        return [
            'id' => $trainer->id,
            'name' => $trainer->name,
            'trainer_code' => $trainer->trainer_code,
            'email' => $canSeeContact ? $trainer->email : null,
            'phone' => $canSeeContact ? $trainer->phone : null,
            'specialization' => $trainer->specialization,
            'classification' => $trainer->classification,
            'status' => $trainer->status,

            'has_tot' => (bool) $trainer->has_tot,
            'is_tot_valid' => (bool) ($trainer->is_tot_valid ?? false),
            'can_actually_train' => (bool) ($trainer->can_actually_train ?? false),

            'training_center' => $trainer->relationLoaded('trainingCenter') ? [
                'id' => $trainer->trainingCenter?->id,
                'name' => $trainer->trainingCenter?->name,
                'code' => $trainer->trainingCenter?->code,
                'city' => $trainer->trainingCenter?->city,
            ] : null,
        ];
    }
}
