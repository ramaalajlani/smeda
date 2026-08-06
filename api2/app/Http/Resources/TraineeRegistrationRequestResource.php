<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ExposesBranchScopeFields;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TraineeRegistrationRequestResource extends JsonResource
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

            'full_name' => $this->full_name,
            'national_id' => $this->national_id,
            'phone' => $this->phone,
            'email' => $this->email,

            'city' => $this->city,
            'address' => $this->address,

            'birth_date' => optional($this->birth_date)?->format('Y-m-d'),
            'gender' => $this->gender,
            'education_level' => $this->education_level,

            'registration_mode' => $this->registration_mode,

            'guardian_name' => $this->guardian_name,
            'guardian_phone' => $this->guardian_phone,
            'guardian_national_id' => $this->guardian_national_id,
            'group_name' => $this->group_name,

            'status' => $this->status,
            'review_notes' => $this->review_notes,

            ...$this->branchScopeFields(),

            'approved_at' => optional($this->approved_at)?->format('Y-m-d H:i:s'),
            'rejected_at' => optional($this->rejected_at)?->format('Y-m-d H:i:s'),

            'submitted_by_user_id' => $this->submitted_by_user_id,
            'reviewed_by_user_id' => $this->reviewed_by_user_id,
            'approved_trainee_id' => $this->approved_trainee_id,

            'submitted_by' => $this->whenLoaded('submittedBy', function () {
                return [
                    'id' => $this->submittedBy?->id,
                    'name' => $this->submittedBy?->name,
                    'email' => $this->submittedBy?->email,
                ];
            }),

            'reviewed_by' => $this->whenLoaded('reviewedBy', function () {
                return [
                    'id' => $this->reviewedBy?->id,
                    'name' => $this->reviewedBy?->name,
                    'email' => $this->reviewedBy?->email,
                ];
            }),

            'approved_trainee' => $this->whenLoaded('approvedTrainee', function () {
                return [
                    'id' => $this->approvedTrainee?->id,
                    'name' => $this->approvedTrainee?->name,
                    'trainee_code' => $this->approvedTrainee?->trainee_code,
                    'national_id' => $this->approvedTrainee?->national_id,
                ];
            }),

            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}