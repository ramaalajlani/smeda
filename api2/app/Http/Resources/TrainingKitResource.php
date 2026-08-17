<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingKitResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_en' => $this->name_en,
            'code' => $this->code,
            'sector' => $this->sector,
            'category' => $this->category,
            'category_id' => $this->category_id,
            'subcategory_id' => $this->subcategory_id,
            'type' => $this->type,
            'material_code' => $this->material_code,
            'level' => $this->level,
            'hours' => $this->hours,
            'suggested_days' => $this->suggested_days,
            'objective' => $this->objective,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'prerequisites' => $this->prerequisites,
            'target_audience' => $this->target_audience,
            'expected_outcomes' => $this->expected_outcomes,
            'status' => $this->status,
            'workflow_status' => $this->workflow_status,
            'published_at' => optional($this->published_at)?->format('Y-m-d H:i:s'),
            'is_active' => (bool) $this->is_active,

            'files' => [
                'promotional' => [
                    'has_file' => $this->hasPromotionalFile(),
                    'original_name' => $this->promotional_file_original_name,
                    'mime' => $this->promotional_file_mime,
                    'size' => $this->promotional_file_size,
                ],
                'training_bag' => [
                    'has_file' => $this->hasTrainingBagFile(),
                    'original_name' => $this->training_bag_file_original_name,
                    'mime' => $this->training_bag_file_mime,
                    'size' => $this->training_bag_file_size,
                ],
            ],

            'training_category' => $this->whenLoaded('trainingCategory', fn () => [
                'id' => $this->trainingCategory->id,
                'name_ar' => $this->trainingCategory->name_ar,
                'slug' => $this->trainingCategory->slug,
            ]),

            'training_subcategory' => $this->whenLoaded('trainingSubcategory', fn () => [
                'id' => $this->trainingSubcategory->id,
                'name_ar' => $this->trainingSubcategory->name_ar,
                'slug' => $this->trainingSubcategory->slug,
            ]),

            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),

            'trainers' => $this->whenLoaded('trainers', function () {
                return $this->trainers->map(function ($trainer) {
                    return [
                        'id' => $trainer->id,
                        'name' => $trainer->name,
                        'trainer_code' => $trainer->trainer_code,
                        'specialization' => $trainer->specialization,
                        'status' => $trainer->status,
                        'training_center_id' => $trainer->training_center_id,
                        'authorization' => [
                            'is_authorized' => (bool) $trainer->pivot->is_authorized,
                            'authorized_from' => $trainer->pivot->authorized_from,
                            'authorized_to' => $trainer->pivot->authorized_to,
                            'notes' => $trainer->pivot->notes,
                        ],
                    ];
                })->values();
            }),

            'centers' => $this->whenLoaded('centers', function () {
                return $this->centers->map(function ($center) {
                    return [
                        'id' => $center->id,
                        'name' => $center->name,
                        'code' => $center->code,
                        'city' => $center->city,
                        'classification' => $center->classification,
                        'accreditation_status' => $center->accreditation_status,
                        'assignment' => [
                            'is_assigned' => (bool) ($center->pivot->is_assigned ?? true),
                            'assigned_from' => $center->pivot->assigned_from,
                            'assigned_to' => $center->pivot->assigned_to,
                            'notes' => $center->pivot->notes,
                        ],
                    ];
                })->values();
            }),

            'programs' => $this->whenLoaded('programs', function () {
                return $this->programs->map(function ($program) {
                    return [
                        'id' => $program->id,
                        'name' => $program->name,
                        'code' => $program->code,
                        'status' => $program->status,
                        'linking' => [
                            'sort_order' => $program->pivot->sort_order,
                            'is_required' => (bool) $program->pivot->is_required,
                        ],
                    ];
                })->values();
            }),

            'stats' => [
                'trainers_count' => $this->whenCounted('trainers', fn () => $this->trainers_count),
                'centers_count' => $this->whenCounted('centers', fn () => $this->centers_count),
                'programs_count' => $this->whenCounted('programs', fn () => $this->programs_count),
                'courses_count' => $this->whenCounted('courses', fn () => $this->courses_count),
                'certificates_count' => $this->whenCounted('certificates', fn () => $this->certificates_count),
            ],

            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}
