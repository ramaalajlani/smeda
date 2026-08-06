<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ExposesBranchScopeFields;
use App\Support\SignedPrintUrl;
use App\Support\TrainingLocationFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingCourseResource extends JsonResource
{
    use ExposesBranchScopeFields;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_code' => $this->course_code,
            'title' => $this->title,
            'delivery_mode' => $this->delivery_mode,
            'approved_platform' => $this->approved_platform,
            'start_date' => optional($this->start_date)->format('Y-m-d'),
            'end_date' => optional($this->end_date)->format('Y-m-d'),
            'planned_hours' => $this->planned_hours,
            'actual_hours' => $this->actual_hours,
            'capacity' => $this->capacity,
            'status' => $this->status,
            'notes' => $this->notes,
            'location' => TrainingLocationFormatter::forCourse($this->resource, $request->user()),

            ...$this->branchScopeFields(),

            'training_center' => $this->whenLoaded('trainingCenter', function () {
                return [
                    'id' => $this->trainingCenter?->id,
                    'name' => $this->trainingCenter?->name,
                    'code' => $this->trainingCenter?->code,
                    'city' => $this->trainingCenter?->city,
                    'classification' => $this->trainingCenter?->classification,
                    'accreditation_status' => $this->trainingCenter?->accreditation_status,
                ];
            }),

            'trainer' => $this->whenLoaded('trainer', function () {
                return [
                    'id' => $this->trainer?->id,
                    'name' => $this->trainer?->name,
                    'trainer_code' => $this->trainer?->trainer_code,
                    'status' => $this->trainer?->status,
                ];
            }),

            'training_kit' => $this->whenLoaded('trainingKit', function () {
                return [
                    'id' => $this->trainingKit?->id,
                    'name' => $this->trainingKit?->name,
                    'code' => $this->trainingKit?->code,
                    'level' => $this->trainingKit?->level,
                    'hours' => $this->trainingKit?->hours,
                    'status' => $this->trainingKit?->status,
                ];
            }),

            'training_program' => $this->whenLoaded('trainingProgram', function () {
                if (!$this->trainingProgram) {
                    return null;
                }

                return [
                    'id' => $this->trainingProgram->id,
                    'name' => $this->trainingProgram->name,
                    'code' => $this->trainingProgram->code,
                    'status' => $this->trainingProgram->status,
                ];
            }),

            'trainees' => $this->whenLoaded('trainees', function () {
                return $this->trainees->map(function ($trainee) {
                    return [
                        'id' => $trainee->id,
                        'name' => $trainee->name,
                        'trainee_code' => $trainee->trainee_code,
                        'status' => $trainee->status,
                        'pivot' => [
                            'attendance_status' => $trainee->pivot?->attendance_status,
                            'result' => $trainee->pivot?->result,
                            'score' => $trainee->pivot?->score,
                            'attended_hours' => $trainee->pivot?->attended_hours,
                            'notes' => $trainee->pivot?->notes,
                        ],
                    ];
                })->values();
            }),

            'certificates' => $this->whenLoaded('certificates', function () {
                return $this->certificates->map(function ($certificate) {
                    return [
                        'id' => $certificate->id,
                        'trainee_id' => $certificate->trainee_id,
                        'trainee_name' => $certificate->trainee?->name,
                        'trainee_code' => $certificate->trainee?->trainee_code,
                        'training_course_id' => $certificate->training_course_id,
                        'certificate_number' => $certificate->certificate_number,
                        'certificate_code' => $certificate->certificate_code,
                        'reference_number' => $certificate->reference_number,
                        'verification_code' => $certificate->verification_code,
                        'certificate_type' => $certificate->certificate_type,
                        'result' => $certificate->result,
                        'score' => $certificate->score,
                        'hours_awarded' => $certificate->hours_awarded,
                        'status' => $certificate->status,
                        'issue_date' => optional($certificate->issue_date)->format('Y-m-d'),
                        'is_verified' => (bool) $certificate->is_verified,
                        'qr_code_path' => $certificate->qr_code_path,
                        'qr_code_url' => ($certificate->status === 'approved' && (bool) $certificate->is_verified && $certificate->certificate_code)
                            ? url('/certificates/' . rawurlencode((string) $certificate->certificate_code) . '/qr')
                            : ($certificate->qr_code_path ? url($certificate->qr_code_path) : null),
                        'view_url' => $certificate->certificate_code
                            ? url('/verify-certificate/' . rawurlencode((string) $certificate->certificate_code))
                            : null,
                        'certificate_file_path' => $certificate->certificate_file_path,
                        'printable_url' => SignedPrintUrl::certificatePrint($certificate->id),
                        'pdf_url' => SignedPrintUrl::certificatePdf($certificate->id),
                    ];
                })->values();
            }),

            'trainees_count' => $this->whenCounted('trainees', $this->trainees_count),
            'certificates_count' => $this->whenCounted('certificates', $this->certificates_count),

            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}