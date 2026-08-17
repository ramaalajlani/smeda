<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingCategoryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'parent' => $this->whenLoaded('parent', fn () => [
                'id' => $this->parent->id,
                'name_ar' => $this->parent->name_ar,
                'slug' => $this->parent->slug,
            ]),
            'children' => TrainingCategoryResource::collection($this->whenLoaded('children')),
            'active_children' => TrainingCategoryResource::collection($this->whenLoaded('activeChildren')),
        ];
    }
}
