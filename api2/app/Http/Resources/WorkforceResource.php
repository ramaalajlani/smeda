<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkforceResource extends JsonResource
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
            'trainee_id' => $this->trainee_id,
            'workforce_code' => $this->workforce_code,
            'status' => $this->status,
            'joined_at' => optional($this->joined_at)?->format('Y-m-d'),
            'notes' => $this->notes,

            'trainee' => $this->whenLoaded('trainee', function () {
                return [
                    'id' => $this->trainee?->id,
                    'name' => $this->trainee?->name,
                    'trainee_code' => $this->trainee?->trainee_code,
                    'national_id' => $this->trainee?->national_id,
                    'phone' => $this->trainee?->phone,
                    'email' => $this->trainee?->email,
                    'city' => $this->trainee?->city,
                    'status' => $this->trainee?->status,
                ];
            }),

            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}