<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingProgramResource extends JsonResource
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
            'description' => $this->description,
            'status' => $this->status,
            'is_active' => (bool) $this->is_active,

            'kits' => $this->whenLoaded('kits', function () {
                return $this->kits->map(function ($kit) {
                    return [
                        'id' => $kit->id,
                        'name' => $kit->name,
                        'code' => $kit->code,
                        'sector' => $kit->sector,
                        'category' => $kit->category,
                        'type' => $kit->type,
                        'level' => $kit->level,
                        'hours' => $kit->hours,
                        'status' => $kit->status,
                        'linking' => [
                            'sort_order' => $kit->pivot->sort_order,
                            'is_required' => (bool) $kit->pivot->is_required,
                        ],
                    ];
                })->sortBy('linking.sort_order')->values();
            }),

            'stats' => [
                'kits_count' => $this->whenCounted('kits', fn () => $this->kits_count),
                'courses_count' => $this->whenCounted('courses', fn () => $this->courses_count),
                'certificates_count' => $this->whenCounted('certificates', fn () => $this->certificates_count),
            ],

            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}