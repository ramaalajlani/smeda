<?php

namespace App\Http\Requests\Training;

use App\Support\CertificateType;

class IssueCertificateRequest extends TrainingFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('issue', \App\Models\Certificate::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('certificate_type')) {
            $this->merge([
                'certificate_type' => CertificateType::normalize($this->input('certificate_type')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'training_course_id' => ['required', 'integer', 'exists:training_courses,id'],
            'trainee_id' => ['required', 'integer', 'exists:trainees,id'],
            'certificate_type' => ['required', 'in:attendance,completion,pass'],
            'result' => ['nullable', 'in:pending,passed,failed,review'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'hours_awarded' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'issued_by' => ['prohibited'],
            'approved_by' => ['prohibited'],
            'reviewed_by' => ['prohibited'],
        ];
    }
}
