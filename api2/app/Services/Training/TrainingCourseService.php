<?php

namespace App\Services\Training;

use App\DTOs\Training\StoreTrainingCourseData;
use App\DTOs\Training\UpdateTrainingCourseData;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\ActiveBranchGuard;
use App\Support\RegistrationBranchResolver;
use App\Support\TrainingDataScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrainingCourseService
{
    public function __construct(
        private EntityCodeGenerator $codeGenerator,
        private TrainingCourseEligibilityService $eligibility,
        private TrainingLocationService $locationService,
        private AuditLogService $auditLog,
    ) {}

    public function createCourse(StoreTrainingCourseData $data, User $user): TrainingCourse
    {
        if ($error = $this->eligibility->assertCenterUserOwnsCenter($user, $data->trainingCenterId)) {
            throw ValidationException::withMessages(['training_center_id' => [$error]]);
        }

        if ($data->deliveryMode === 'online' && empty($data->approvedPlatform)) {
            throw ValidationException::withMessages(['approved_platform' => ['يجب تحديد المنصة المعتمدة للدورات الإلكترونية.']]);
        }

        foreach ([
            fn () => $this->eligibility->validateCenterForCourse($data->trainingCenterId, $data->deliveryMode),
            fn () => $this->eligibility->validateTrainerForCourse($data->trainerId, $data->trainingCenterId, $data->trainingKitId),
            fn () => $this->eligibility->validateKitForCourse($data->trainingKitId),
        ] as $validator) {
            if ($message = $validator()) {
                throw ValidationException::withMessages(['course' => [$message]]);
            }
        }

        $center = TrainingCenter::query()->findOrFail($data->trainingCenterId);
        ActiveBranchGuard::assertCourseBranchActive($center->branch_id);
        $payload = array_merge([
            'delivery_mode' => $data->deliveryMode,
            'approved_platform' => $data->approvedPlatform,
        ], $data->locationFields);

        if ($message = $this->locationService->validateOfflineCourseLocation($payload, $center)) {
            throw ValidationException::withMessages(['location' => [$message]]);
        }

        $locationData = $this->locationService->resolveCourseLocationData($payload, $center);
        $branchScope = RegistrationBranchResolver::fromTrainingCenter($center->id);

        $course = DB::transaction(function () use ($data, $locationData, $user, $branchScope) {
            $course = TrainingCourse::create(array_merge([
                'training_center_id' => $data->trainingCenterId,
                'trainer_id' => $data->trainerId,
                'training_kit_id' => $data->trainingKitId,
                'training_program_id' => $data->trainingProgramId,
                'course_code' => $this->codeGenerator->nextCourseCode(),
                'title' => $data->title,
                'delivery_mode' => $data->deliveryMode,
                'approved_platform' => $data->deliveryMode === 'online' ? $data->approvedPlatform : null,
                'start_date' => $data->startDate,
                'end_date' => $data->endDate,
                'planned_hours' => $data->plannedHours,
                'actual_hours' => $data->actualHours,
                'capacity' => $data->capacity,
                'status' => $data->status,
                'notes' => $data->notes,
                'branch_id' => $branchScope['branch_id'],
                'governorate_id' => $branchScope['governorate_id'],
            ], $locationData));

            $this->auditLog->log('course_created', $user, $course, null, [
                'course_code' => $course->course_code,
                'training_center_id' => $course->training_center_id,
            ]);

            return $course;
        });

        return $course->load($this->defaultRelations());
    }

    public function updateCourse(TrainingCourse $course, UpdateTrainingCourseData $data, User $user): TrainingCourse
    {
        if (!$course->canBeModified()) {
            throw ValidationException::withMessages(['course' => ['لا يمكن تعديل دورة ملغاة.']]);
        }

        $validated = array_merge($data->fields, $data->locationFields);

        if (
            isset($validated['start_date'], $validated['end_date']) &&
            $validated['start_date'] > $validated['end_date']
        ) {
            throw ValidationException::withMessages(['end_date' => ['تاريخ البداية يجب أن يكون قبل تاريخ النهاية.']]);
        }

        if ($course->status === 'completed') {
            $forbiddenOnCompleted = array_diff(array_keys($validated), ['notes', 'actual_hours']);
            if ($forbiddenOnCompleted !== []) {
                throw ValidationException::withMessages(['course' => ['لا يمكن تعديل بيانات الدورة المكتملة إلا الملاحظات أو الساعات الفعلية.']]);
            }
        }

        $hasCertificates = $course->certificates()
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->exists();

        if ($hasCertificates && !TrainingDataScope::hasUnrestrictedTrainingAccess($user)) {
            foreach (['training_center_id', 'trainer_id', 'training_kit_id', 'planned_hours'] as $field) {
                if (array_key_exists($field, $validated) && (int) $validated[$field] !== (int) $course->{$field}) {
                    throw ValidationException::withMessages([$field => ['لا يمكن تغيير ' . $field . ' بعد إصدار شهادات للدورة.']]);
                }
            }
        }

        $nextCenterId = $validated['training_center_id'] ?? $course->training_center_id;
        $nextTrainerId = $validated['trainer_id'] ?? $course->trainer_id;
        $nextKitId = $validated['training_kit_id'] ?? $course->training_kit_id;
        $nextDeliveryMode = $validated['delivery_mode'] ?? $course->delivery_mode;
        $nextPlatform = array_key_exists('approved_platform', $validated)
            ? $validated['approved_platform']
            : $course->approved_platform;

        if ($error = $this->eligibility->assertCenterUserOwnsCenter($user, (int) $nextCenterId)) {
            throw ValidationException::withMessages(['training_center_id' => [$error]]);
        }

        if ($nextDeliveryMode === 'online' && empty($nextPlatform)) {
            throw ValidationException::withMessages(['approved_platform' => ['يجب تحديد المنصة المعتمدة للدورات الإلكترونية.']]);
        }

        if (array_key_exists('training_center_id', $validated) || array_key_exists('delivery_mode', $validated)) {
            if ($message = $this->eligibility->validateCenterForCourse((int) $nextCenterId, $nextDeliveryMode)) {
                throw ValidationException::withMessages(['training_center_id' => [$message]]);
            }
        }

        if (
            array_key_exists('trainer_id', $validated) ||
            array_key_exists('training_center_id', $validated) ||
            array_key_exists('training_kit_id', $validated)
        ) {
            if ($message = $this->eligibility->validateTrainerForCourse((int) $nextTrainerId, (int) $nextCenterId, (int) $nextKitId)) {
                throw ValidationException::withMessages(['trainer_id' => [$message]]);
            }
        }

        if (array_key_exists('training_kit_id', $validated)) {
            if ($message = $this->eligibility->validateKitForCourse((int) $nextKitId)) {
                throw ValidationException::withMessages(['training_kit_id' => [$message]]);
            }
        }

        if (isset($validated['capacity']) && (int) $validated['capacity'] < (int) $course->registered_trainees_count) {
            throw ValidationException::withMessages(['capacity' => ['لا يمكن أن تكون السعة أقل من عدد المتدربين المسجلين حالياً.']]);
        }

        $updateData = $validated;
        if (($updateData['delivery_mode'] ?? $course->delivery_mode) === 'offline') {
            $updateData['approved_platform'] = null;
        } else {
            $updateData = array_merge($updateData, [
                'venue_name' => null,
                'governorate' => null,
                'city' => null,
                'district' => null,
                'address' => null,
                'latitude' => null,
                'longitude' => null,
            ]);
        }

        $center = TrainingCenter::query()->findOrFail($nextCenterId);
        $mergedForLocation = array_merge($course->toArray(), $updateData);

        if ($message = $this->locationService->validateOfflineCourseLocation($mergedForLocation, $center)) {
            throw ValidationException::withMessages(['location' => [$message]]);
        }

        if (($updateData['delivery_mode'] ?? $course->delivery_mode) === 'offline') {
            $updateData = array_merge($updateData, $this->locationService->resolveCourseLocationData($mergedForLocation, $center));
        }

        $oldValues = $course->only(array_keys($updateData));
        $course->update($updateData);

        $this->auditLog->log('course_updated', $user, $course, $oldValues, $updateData);

        return $course->refresh()->load($this->defaultRelations());
    }

    public function completeCourse(TrainingCourse $course, User $user): TrainingCourse
    {
        if (!$course->canBeCompleted()) {
            throw ValidationException::withMessages(['course' => ['لا يمكن إكمال هذه الدورة في حالتها الحالية.']]);
        }

        if ($course->trainees()->count() === 0) {
            throw ValidationException::withMessages(['course' => ['لا يمكن إكمال دورة بدون متدربين.']]);
        }

        DB::transaction(function () use ($course, $user) {
            $course->update([
                'status' => 'completed',
                'actual_hours' => $course->actual_hours ?: $course->planned_hours,
            ]);

            $this->auditLog->log('course_completed', $user, $course, null, ['status' => 'completed']);
        });

        return $course->refresh()->load($this->defaultRelations())->loadCount(['trainees', 'certificates']);
    }

    public function defaultRelations(): array
    {
        return [
            'trainingCenter:id,name,code,city,classification,accreditation_status',
            'trainer:id,name,trainer_code,specialization,has_tot,can_train,status',
            'trainingKit:id,name,code,sector,category,type,level,hours,status',
            'trainingProgram:id,name,code,status',
            'branch:id,name,governorate_id',
            'governorate:id,name_ar',
        ];
    }
}
