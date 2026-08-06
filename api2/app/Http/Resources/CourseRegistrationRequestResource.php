<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ExposesBranchScopeFields;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseRegistrationRequestResource extends JsonResource
{
    use ExposesBranchScopeFields;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_number' => $this->request_number,

            'training_course_id' => $this->training_course_id,
            'registration_mode' => $this->registration_mode,

            'submitted_by_user_id' => $this->submitted_by_user_id,
            'submitted_by_type' => $this->submitted_by_type,

            'applicant_name' => $this->applicant_name,
            'applicant_phone' => $this->applicant_phone,
            'applicant_email' => $this->applicant_email,

            'guardian_name' => $this->guardian_name,
            'guardian_phone' => $this->guardian_phone,
            'guardian_national_id' => $this->guardian_national_id,

            'notes' => $this->notes,
            'status' => $this->status,

            ...$this->branchScopeFields(),

            'guardian_confirmed_at' => optional($this->guardian_confirmed_at)?->format('Y-m-d H:i:s'),
            'completed_at' => optional($this->completed_at)?->format('Y-m-d H:i:s'),

            'training_course' => $this->whenLoaded('trainingCourse', function () {
                return [
                    'id' => $this->trainingCourse?->id,
                    'course_code' => $this->trainingCourse?->course_code,
                    'title' => $this->trainingCourse?->title,
                    'delivery_mode' => $this->trainingCourse?->delivery_mode,
                    'status' => $this->trainingCourse?->status,
                    'capacity' => $this->trainingCourse?->capacity,
                ];
            }),

            'submitted_by' => $this->whenLoaded('submittedBy', function () {
                return [
                    'id' => $this->submittedBy?->id,
                    'name' => $this->submittedBy?->name,
                    'email' => $this->submittedBy?->email,
                ];
            }),

            'members' => $this->whenLoaded('members', function () {
                return CourseRegistrationRequestMemberResource::collection($this->members);
            }),

            'members_count' => $this->whenLoaded('members', function () {
                return $this->members->count();
            }),

            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}