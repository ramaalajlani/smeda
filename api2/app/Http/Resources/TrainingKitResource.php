<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingKitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'sector' => $this->sector,
            'category' => $this->category,
            'type' => $this->type,
            'material_code' => $this->material_code,
            'level' => $this->level,
            'hours' => $this->hours,
            'objective' => $this->objective,
            'description' => $this->description,
            'status' => $this->status,
            'is_active' => (bool) $this->is_active,

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