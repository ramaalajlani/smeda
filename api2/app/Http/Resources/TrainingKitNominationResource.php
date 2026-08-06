<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingKitNominationResource extends JsonResource
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

            'trainer_id' => $this->trainer_id,
            'training_kit_id' => $this->training_kit_id,

            'proposed_name' => $this->proposed_name,
            'description' => $this->description,
            'sector' => $this->sector,
            'category' => $this->category,
            'hours' => $this->hours,

            'status' => $this->status,
            'decision_notes' => $this->decision_notes,
            'decided_at' => optional($this->decided_at)?->format('Y-m-d H:i:s'),

            'trainer' => $this->whenLoaded('trainer', function () {
                return [
                    'id' => $this->trainer?->id,
                    'name' => $this->trainer?->name,
                    'trainer_code' => $this->trainer?->trainer_code,
                    'specialization' => $this->trainer?->specialization,
                    'classification' => $this->trainer?->classification,
                    'status' => $this->trainer?->status,
                    'has_tot' => (bool) $this->trainer?->has_tot,
                    'is_tot_valid' => (bool) ($this->trainer?->is_tot_valid ?? false),
                    'can_actually_train' => (bool) ($this->trainer?->can_actually_train ?? false),
                ];
            }),

            'training_kit' => $this->whenLoaded('trainingKit', function () {
                return [
                    'id' => $this->trainingKit?->id,
                    'name' => $this->trainingKit?->name,
                    'code' => $this->trainingKit?->code,
                    'sector' => $this->trainingKit?->sector,
                    'category' => $this->trainingKit?->category,
                    'level' => $this->trainingKit?->level,
                    'hours' => $this->trainingKit?->hours,
                    'status' => $this->trainingKit?->status,
                ];
            }),

            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}