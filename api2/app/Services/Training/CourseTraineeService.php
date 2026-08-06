<?php

namespace App\Services\Training;

use App\DTOs\Training\UpdateCourseTraineeResultData;
use App\Models\Trainee;
use App\Models\TrainingCourse;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\TrainingDataScope;
use Illuminate\Validation\ValidationException;

class CourseTraineeService
{
    public function __construct(
        private AuditLogService $auditLog,
    ) {}

    public function addTrainee(TrainingCourse $course, int $traineeId, ?string $notes, User $user): Trainee
    {
        $this->ensureCourseOpen($course);
        $this->ensureCapacity($course);

        if ($course->hasTrainee($traineeId)) {
            throw ValidationException::withMessages(['trainee_id' => ['المتدرب مسجل مسبقاً في هذه الدورة.']]);
        }

        $trainee = Trainee::query()->findOrFail($traineeId);

        if ($trainee->status !== 'active') {
            throw ValidationException::withMessages(['trainee_id' => ['المتدرب غير نشط.']]);
        }

        $course->trainees()->attach($traineeId, [
            'attendance_status' => 'registered',
            'result' => 'pending',
            'score' => null,
            'attended_hours' => 0,
            'notes' => $notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->auditLog->log('course_trainee_added', $user, $course, null, [
            'trainee_id' => $traineeId,
            'course_id' => $course->id,
        ]);

        return $course->trainees()->where('trainees.id', $traineeId)->firstOrFail();
    }

    public function updateResult(
        TrainingCourse $course,
        int $traineeId,
        UpdateCourseTraineeResultData $data,
        User $user
    ): Trainee {
        if (!$course->hasTrainee($traineeId)) {
            throw ValidationException::withMessages(['trainee_id' => ['المتدرب غير مسجل في هذه الدورة.']]);
        }

        $this->ensureNoIssuedCertificate($course, $traineeId, $user);

        $maxHours = $course->resolved_hours;

        if ($data->attendedHours !== null && $data->attendedHours > $maxHours) {
            throw ValidationException::withMessages([
                'attended_hours' => ['ساعات الحضور لا يمكن أن تتجاوز ساعات الدورة (' . $maxHours . ').'],
            ]);
        }

        $pivot = $course->trainees()->where('trainees.id', $traineeId)->first()?->pivot;
        $pivotData = $data->toPivotArray();
        $nextResult = $pivotData['result'] ?? $pivot?->result;
        $nextScore = array_key_exists('score', $pivotData) ? $pivotData['score'] : $pivot?->score;

        if ($nextResult === 'passed' && $nextScore === null) {
            throw ValidationException::withMessages(['score' => ['يجب تحديد الدرجة عند تعيين نتيجة اجتياز.']]);
        }

        $pivotData = array_merge($data->toPivotArray(), ['updated_at' => now()]);
        $course->trainees()->updateExistingPivot($traineeId, $pivotData);

        $this->auditLog->log('course_trainee_result_updated', $user, $course, null, [
            'trainee_id' => $traineeId,
            'changes' => $pivotData,
        ]);

        return $course->trainees()->where('trainees.id', $traineeId)->firstOrFail();
    }

    public function removeTrainee(TrainingCourse $course, int $traineeId, User $user): void
    {
        $this->ensureCourseOpen($course);

        if (!$course->hasTrainee($traineeId)) {
            throw ValidationException::withMessages(['trainee_id' => ['المتدرب غير مسجل في هذه الدورة.']]);
        }

        if ($course->hasCertificateForTrainee($traineeId)) {
            throw ValidationException::withMessages(['trainee_id' => ['لا يمكن إزالة متدرب لديه شهادة صادرة ضمن هذه الدورة.']]);
        }

        $course->trainees()->detach($traineeId);

        $this->auditLog->log('course_trainee_removed', $user, $course, null, [
            'trainee_id' => $traineeId,
        ]);
    }

    public function formatCourseTrainee(?Trainee $trainee, TrainingCourse $course, ?bool $hasCertificate = null): ?array
    {
        if (!$trainee) {
            return null;
        }

        return [
            'id' => $trainee->id,
            'name' => $trainee->name,
            'trainee_code' => $trainee->trainee_code,
            'status' => $trainee->status,
            'pivot' => [
                'attendance_status' => $trainee->pivot?->attendance_status,
                'result' => $trainee->pivot?->result,
                'score' => $trainee->pivot?->score,
                'attended_hours' => (int) ($trainee->pivot?->attended_hours ?? 0),
                'notes' => $trainee->pivot?->notes,
            ],
            'has_certificate' => $hasCertificate ?? $course->hasCertificateForTrainee($trainee->id),
        ];
    }

    private function ensureCourseOpen(TrainingCourse $course): void
    {
        if ($course->status === 'completed') {
            throw ValidationException::withMessages(['course' => ['لا يمكن تعديل متدربي دورة مكتملة.']]);
        }
    }

    private function ensureCapacity(TrainingCourse $course): void
    {
        if (!$course->hasAvailableCapacity()) {
            throw ValidationException::withMessages(['capacity' => ['لا توجد سعة متاحة في هذه الدورة.']]);
        }
    }

    private function ensureNoIssuedCertificate(TrainingCourse $course, int $traineeId, User $user): void
    {
        if (
            $course->hasApprovedCertificateForTrainee($traineeId) &&
            !TrainingDataScope::hasUnrestrictedTrainingAccess($user)
        ) {
            throw ValidationException::withMessages(['trainee_id' => ['لا يمكن تعديل نتائج متدرب لديه شهادة معتمدة.']]);
        }

        if (
            $course->hasCertificateForTrainee($traineeId) &&
            !TrainingDataScope::hasUnrestrictedTrainingAccess($user)
        ) {
            throw ValidationException::withMessages(['trainee_id' => ['لا يمكن تعديل نتائج متدرب لديه شهادة صادرة.']]);
        }
    }
}
