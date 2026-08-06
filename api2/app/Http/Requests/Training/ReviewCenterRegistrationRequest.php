<?php

namespace App\Http\Requests\Training;

use App\Models\TrainingCenterRegistrationRequest;

class ReviewCenterRegistrationRequest extends ReviewRegistrationRequest
{
    public function authorize(): bool
    {
        $row = TrainingCenterRegistrationRequest::query()->find($this->route('id'));

        return $row && $this->user()?->can('review', $row);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['required', 'in:approved,rejected,under_review'],
        ]);
    }
}
