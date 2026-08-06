<?php

namespace App\Http\Resources;

use App\Support\TrainingDataScope;
use App\Support\SignedPrintUrl;
use App\Support\TrainingLocationFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $canSeeContact = TrainingDataScope::canViewTrainerContact(
            $user,
            $this->id,
            $this->training_center_id
        );

        $isTotValid = (bool) ($this->is_tot_valid ?? false);
        $canActuallyTrain = (bool) ($this->can_actually_train ?? false);

        return [
            'id' => $this->id,
            'trainer_code' => $this->trainer_code,
            'name' => $this->name,
            'phone' => $this->when($canSeeContact, $this->phone),
            'email' => $this->when($canSeeContact, $this->email),
            'specialization' => $this->specialization,
            'classification' => $this->classification,
            'location' => TrainingLocationFormatter::forTrainer($this->resource, $user),

            'has_tot' => (bool) $this->has_tot,
            'tot_certificate_number' => $this->tot_certificate_number,
            'tot_certificate_source' => $this->tot_certificate_source,
            'tot_issue_date' => optional($this->tot_issue_date)?->format('Y-m-d'),
            'tot_expiry_date' => optional($this->tot_expiry_date)?->format('Y-m-d'),
            'is_tot_valid' => $isTotValid,

            'can_train' => (bool) $this->can_train,
            'can_evaluate' => (bool) $this->can_evaluate,
            'can_actually_train' => $canActuallyTrain,

            'status' => $this->status,
            'accreditation_start_date' => optional($this->accreditation_start_date)?->format('Y-m-d'),
            'accreditation_end_date' => optional($this->accreditation_end_date)?->format('Y-m-d'),

            'eligibility_status' => $canActuallyTrain
                ? 'eligible'
                : 'not_eligible',

            'eligibility_label' => $canActuallyTrain
                ? 'مدرب معتمد ومؤهل للتدريب'
                : 'مدرب غير مؤهل للتدريب حالياً',

            'card_url' => SignedPrintUrl::trainerCard($this->id),
            'pdf_url' => SignedPrintUrl::trainerCardPdf($this->id),

            'training_center' => $this->whenLoaded('trainingCenter', function () {
                return [
                    'id' => $this->trainingCenter->id,
                    'name' => $this->trainingCenter->name,
                    'code' => $this->trainingCenter->code,
                    'city' => $this->trainingCenter->city,
                    'classification' => $this->trainingCenter->classification,
                    'accreditation_status' => $this->trainingCenter->accreditation_status,
                ];
            }),

            'profile' => $this->whenLoaded('profile', function () {
                return [
                    'id' => $this->profile?->id,
                    'headline' => $this->profile?->headline,
                    'bio' => $this->profile?->bio,
                    'experience_years' => $this->profile?->experience_years,
                    'skills' => $this->profile?->skills,
                    'special_interests' => $this->profile?->special_interests,
                    'linkedin_summary' => $this->profile?->linkedin_summary,
                    'cv_file' => $this->profile?->cv_file,
                    'profile_image' => $this->profile?->profile_image,
                    'visibility' => $this->profile?->visibility,
                ];
            }),

            'kits' => $this->whenLoaded('kits', function () {
                return $this->kits->map(function ($kit) {
                    return [
                        'id' => $kit->id,
                        'name' => $kit->name,
                        'code' => $kit->code,
                        'sector' => $kit->sector,
                        'category' => $kit->category,
                        'level' => $kit->level,
                        'hours' => $kit->hours,
                        'status' => $kit->status,
                        'authorization' => [
                            'is_authorized' => (bool) $kit->pivot->is_authorized,
                            'authorized_from' => $kit->pivot->authorized_from,
                            'authorized_to' => $kit->pivot->authorized_to,
                            'notes' => $kit->pivot->notes,
                        ],
                    ];
                })->values();
            }),

            'stats' => [
                'kits_count' => $this->whenCounted('kits', fn () => $this->kits_count),
                'courses_count' => $this->whenCounted('courses', fn () => $this->courses_count),
            ],

            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}