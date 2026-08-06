<?php

namespace App\Http\Requests\Training;

class ApproveCertificateRequest extends TrainingFormRequest
{
    public function authorize(): bool
    {
        $step = $this->input('approval_step');

        if (!$step) {
            return $this->userHasPermission('approve_center_certificates')
                || $this->userHasPermission('approve_training_certificates')
                || $this->userHasPermission('approve_deputy_certificates')
                || $this->userHasPermission('approve_general_director_certificates');
        }

        return match ($step) {
            'center_approval' => $this->userHasPermission('approve_center_certificates'),
            'training_manager_approval' => $this->userHasPermission('approve_training_certificates'),
            'deputy_director_approval' => $this->userHasPermission('approve_deputy_certificates'),
            'general_director_approval' => $this->userHasPermission('approve_general_director_certificates'),
            default => false,
        };
    }

    public function rules(): array
    {
        return [
            'approval_step' => ['required', 'in:center_approval,training_manager_approval,deputy_director_approval,general_director_approval'],
            'decision' => ['required', 'in:approved,rejected'],
            'notes' => ['nullable', 'string'],
            'approved_by' => ['prohibited'],
            'issued_by' => ['prohibited'],
            'reviewed_by' => ['prohibited'],
        ];
    }
}
