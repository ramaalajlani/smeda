<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseRegistrationRequestMemberResource extends JsonResource
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
            'course_registration_request_id' => $this->course_registration_request_id,
            'trainee_id' => $this->trainee_id,

            'full_name' => $this->full_name,
            'national_id' => $this->national_id,
            'phone' => $this->phone,
            'email' => $this->email,

            'birth_date' => optional($this->birth_date)?->format('Y-m-d'),
            'gender' => $this->gender,
            'education_level' => $this->education_level,

            'relation_type' => $this->relation_type,
            'status' => $this->status,
            'notes' => $this->notes,

            'trainee' => $this->whenLoaded('trainee', function () {
                return [
                    'id' => $this->trainee?->id,
                    'name' => $this->trainee?->name,
                    'trainee_code' => $this->trainee?->trainee_code,
                    'national_id' => $this->trainee?->national_id,
                ];
            }),

            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}