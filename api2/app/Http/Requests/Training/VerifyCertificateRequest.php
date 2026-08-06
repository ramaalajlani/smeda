<?php

namespace App\Http\Requests\Training;

class VerifyCertificateRequest extends TrainingFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => ['required', 'string'],
            'type' => ['required', 'in:certificate_number,certificate_code,reference_number,verification_code'],
        ];
    }
}
