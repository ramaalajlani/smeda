<?php

namespace App\Http\Requests\Training;

use App\Models\TrainingCourse;

class CompleteTrainingCourseRequest extends TrainingFormRequest
{
    public function authorize(): bool
    {
        $course = TrainingCourse::query()->find($this->route('id'));

        return $course && $this->user()?->can('complete', $course);
    }

    public function rules(): array
    {
        return [];
    }
}
