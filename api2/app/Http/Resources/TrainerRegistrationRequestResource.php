<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ExposesBranchScopeFields;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerRegistrationRequestResource extends JsonResource
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

            'training_center_id' => $this->training_center_id,

            'full_name' => $this->full_name,
            'national_id' => $this->national_id,
            'phone' => $this->phone,
            'email' => $this->email,

            'specialization' => $this->specialization,
            'classification_requested' => $this->classification_requested,

            'has_tot' => (bool) $this->has_tot,
            'tot_certificate_number' => $this->tot_certificate_number,
            'tot_certificate_source' => $this->tot_certificate_source,
            'tot_issue_date' => optional($this->tot_issue_date)?->format('Y-m-d'),
            'tot_expiry_date' => optional($this->tot_expiry_date)?->format('Y-m-d'),

            'cv_file' => $this->cv_file,
            'certificate_file' => $this->certificate_file,

            'cv_file_url' => $this->cv_file ? url($this->cv_file) : null,
            'certificate_file_url' => $this->certificate_file ? url($this->certificate_file) : null,

            'status' => $this->status,
            'review_notes' => $this->review_notes,

            ...$this->branchScopeFields(),

            'approved_at' => optional($this->approved_at)?->format('Y-m-d H:i:s'),
            'rejected_at' => optional($this->rejected_at)?->format('Y-m-d H:i:s'),

            'submitted_by_user_id' => $this->submitted_by_user_id,
            'reviewed_by_user_id' => $this->reviewed_by_user_id,
            'approved_trainer_id' => $this->approved_trainer_id,

            'training_center' => $this->whenLoaded('trainingCenter', function () {
                return [
                    'id' => $this->trainingCenter?->id,
                    'name' => $this->trainingCenter?->name,
                    'code' => $this->trainingCenter?->code,
                    'city' => $this->trainingCenter?->city,
                ];
            }),

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

            'approved_trainer' => $this->whenLoaded('approvedTrainer', function () {
                return [
                    'id' => $this->approvedTrainer?->id,
                    'name' => $this->approvedTrainer?->name,
                    'trainer_code' => $this->approvedTrainer?->trainer_code,
                    'training_center_id' => $this->approvedTrainer?->training_center_id,
                ];
            }),

            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}