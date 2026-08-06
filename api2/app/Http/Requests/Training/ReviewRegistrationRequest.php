<?php

namespace App\Http\Requests\Training;

class ReviewRegistrationRequest extends TrainingFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'string'],
            'decision_notes' => ['nullable', 'string'],
            'review_notes' => ['nullable', 'string'],
        ];
    }
}
