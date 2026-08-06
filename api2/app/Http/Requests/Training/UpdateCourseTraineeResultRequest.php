<?php

namespace App\Http\Requests\Training;

use App\Models\TrainingCourse;

class UpdateCourseTraineeResultRequest extends TrainingFormRequest
{
    public function authorize(): bool
    {
        $course = TrainingCourse::query()->find($this->route('id'));

        return $course && $this->user()?->can('updateTraineeResult', $course);
    }

    public function rules(): array
    {
        return [
            'attendance_status' => ['sometimes', 'in:registered,attended,absent,withdrawn,completed'],
            'result' => ['sometimes', 'in:pending,passed,failed,attendance_only'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'attended_hours' => ['sometimes', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
