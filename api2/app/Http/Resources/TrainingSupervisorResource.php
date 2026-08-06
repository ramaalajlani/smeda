<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingSupervisorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'parent_id' => $this->parent_id,
            'branch_id' => $this->branch_id,
            'governorate_id' => $this->governorate_id,
            'is_active' => (bool) $this->is_active,
            'notes' => $this->notes,
            'parent' => $this->whenLoaded('parent', fn () => [
                'id' => $this->parent?->id,
                'name' => $this->parent?->name,
                'code' => $this->parent?->code,
                'type' => $this->parent?->type,
            ]),
            'branch' => $this->whenLoaded('branch', fn () => [
                'id' => $this->branch?->id,
                'name' => $this->branch?->name,
            ]),
            'governorate' => $this->whenLoaded('governorate', fn () => [
                'id' => $this->governorate?->id,
                'name_ar' => $this->governorate?->name_ar,
            ]),
            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}
