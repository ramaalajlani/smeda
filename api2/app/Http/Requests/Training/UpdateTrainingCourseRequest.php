<?php

namespace App\Http\Requests\Training;

use App\Models\TrainingCourse;
use App\Services\Training\TrainingLocationService;

class UpdateTrainingCourseRequest extends TrainingFormRequest
{
    public function authorize(): bool
    {
        $course = TrainingCourse::query()->find($this->route('id'));

        return $course && $this->user()?->can('update', $course);
    }

    public function rules(): array
    {
        $locationService = app(TrainingLocationService::class);

        return array_merge([
            'training_center_id' => ['sometimes', 'integer', 'exists:training_centers,id'],
            'trainer_id' => ['sometimes', 'integer', 'exists:trainers,id'],
            'training_kit_id' => ['sometimes', 'integer', 'exists:training_kits,id'],
            'training_program_id' => ['nullable', 'integer', 'exists:training_programs,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'delivery_mode' => ['sometimes', 'in:online,offline'],
            'approved_platform' => ['nullable', 'string', 'max:150'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'planned_hours' => ['sometimes', 'integer', 'min:1'],
            'actual_hours' => ['nullable', 'integer', 'min:0'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'in:draft,scheduled,ongoing'],
            'notes' => ['nullable', 'string'],
        ], $locationService->locationValidationRules(true));
    }
}
