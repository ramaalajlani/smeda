<?php

namespace App\Http\Requests\Training;

use App\Models\TrainingCourse;

class AddCourseTraineeRequest extends TrainingFormRequest
{
    public function authorize(): bool
    {
        $course = TrainingCourse::query()->find($this->route('id'));

        return $course && $this->user()?->can('manageTrainees', $course);
    }

    public function rules(): array
    {
        return [
            'trainee_id' => ['required', 'integer', 'exists:trainees,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
