<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ExposesBranchScopeFields;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingCenterRegistrationRequestResource extends JsonResource
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

            'center_name' => $this->center_name,
            'city' => $this->city,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'classification_requested' => $this->classification_requested,

            'supports_offline_training' => (bool) $this->supports_offline_training,
            'supports_online_training' => (bool) $this->supports_online_training,

            'license_file' => $this->license_file,
            'accreditation_file' => $this->accreditation_file,

            'license_file_url' => $this->license_file ? url($this->license_file) : null,
            'accreditation_file_url' => $this->accreditation_file ? url($this->accreditation_file) : null,

            'status' => $this->status,
            'review_notes' => $this->review_notes,

            ...$this->branchScopeFields(),

            'approved_at' => optional($this->approved_at)?->format('Y-m-d H:i:s'),
            'rejected_at' => optional($this->rejected_at)?->format('Y-m-d H:i:s'),

            'submitted_by_user_id' => $this->submitted_by_user_id,
            'reviewed_by_user_id' => $this->reviewed_by_user_id,
            'approved_training_center_id' => $this->approved_training_center_id,

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

            'approved_training_center' => $this->whenLoaded('approvedTrainingCenter', function () {
                return [
                    'id' => $this->approvedTrainingCenter?->id,
                    'name' => $this->approvedTrainingCenter?->name,
                    'code' => $this->approvedTrainingCenter?->code,
                    'city' => $this->approvedTrainingCenter?->city,
                ];
            }),

            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}