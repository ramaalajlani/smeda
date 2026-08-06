<?php

namespace App\Http\Requests\Training;

use App\Services\Training\TrainingLocationService;

class StoreTrainingCourseRequest extends TrainingFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\TrainingCourse::class) ?? false;
    }

    public function rules(): array
    {
        $locationService = app(TrainingLocationService::class);

        return array_merge([
            'training_center_id' => ['required', 'integer', 'exists:training_centers,id'],
            'trainer_id' => ['required', 'integer', 'exists:trainers,id'],
            'training_kit_id' => ['required', 'integer', 'exists:training_kits,id'],
            'training_program_id' => ['nullable', 'integer', 'exists:training_programs,id'],
            'title' => ['required', 'string', 'max:255'],
            'delivery_mode' => ['required', 'in:online,offline'],
            'approved_platform' => ['nullable', 'string', 'max:150'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'planned_hours' => ['required', 'integer', 'min:1'],
            'actual_hours' => ['nullable', 'integer', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['nullable', 'in:draft,scheduled,ongoing'],
            'notes' => ['nullable', 'string'],
        ], $locationService->locationValidationRules());
    }
}
