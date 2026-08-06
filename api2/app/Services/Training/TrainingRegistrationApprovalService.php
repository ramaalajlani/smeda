<?php

namespace App\Services\Training;

use App\DTOs\Training\ReviewRegistrationRequestData;
use App\Models\Trainee;
use App\Models\TraineeRegistrationRequest;
use App\Models\Trainer;
use App\Models\TrainerRegistrationRequest;
use App\Models\TrainingCenter;
use App\Models\TrainingCenterRegistrationRequest;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\RegistrationApprovalLinker;
use App\Support\RegistrationBranchResolver;
use App\Support\TrainingSupervisorResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrainingRegistrationApprovalService
{
    public function __construct(
        private EntityCodeGenerator $codeGenerator,
        private AuditLogService $auditLog,
        private TrainingSupervisorResolver $supervisorResolver,
    ) {}

    public function approveCenterRequest(
        TrainingCenterRegistrationRequest $row,
        ReviewRegistrationRequestData $data,
        User $reviewer
    ): TrainingCenterRegistrationRequest {
        if ($row->isApproved() && $row->approved_training_center_id) {
            throw ValidationException::withMessages([
                'status' => ['تم اعتماد هذا الطلب مسبقاً ومرتبط بمركز تدريبي.'],
            ]);
        }

        if (!$row->isPending() && !$row->isUnderReview()) {
            throw ValidationException::withMessages([
                'status' => ['لا يمكن اعتماد هذا الطلب في حالته الحالية.'],
            ]);
        }

        DB::transaction(function () use ($row, $data, $reviewer) {
            $approvedCenterId = $row->approved_training_center_id;

            if ($approvedCenterId) {
                $center = TrainingCenter::query()->findOrFail($approvedCenterId);
            } else {
                $center = TrainingCenter::create(array_merge([
                    'name' => $row->center_name,
                    'code' => $this->codeGenerator->nextCenterCode(),
                    'city' => $row->city,
                    'address' => $row->address,
                    'phone' => $row->phone,
                    'email' => $row->email,
                    'classification' => $row->classification_requested,
                    'accreditation_status' => 'approved',
                    'supports_offline_training' => (bool) $row->supports_offline_training,
                    'supports_online_training' => (bool) $row->supports_online_training,
                    'accreditation_start_date' => now()->toDateString(),
                    'accreditation_end_date' => now()->addYear()->toDateString(),
                    'latitude' => $row->latitude,
                    'longitude' => $row->longitude,
                    'location_visibility' => 'public',
                    'license_number' => $row->license_number,
                    'license_issue_date' => $row->license_issue_date,
                    'license_expiry_date' => null,
                    'license_issued_by' => $row->license_issued_by,
                    'license_image_path' => $row->license_image_path,
                    'is_active' => true,
                    'supervisor_id' => $this->resolveSupervisorId(
                        $row->branch_id ? (int) $row->branch_id : null,
                        $row->governorate_id ? (int) $row->governorate_id : null
                    ),
                    'notes' => 'Approved from registration request #' . $row->request_number,
                ], $this->scopeFromRegistrationRow($row)));

                $approvedCenterId = $center->id;
            }

            if ($row->submitted_by_user_id) {
                $submitter = User::query()->find($row->submitted_by_user_id);
                if ($submitter) {
                    RegistrationApprovalLinker::linkUserToCenter(
                        $submitter,
                        TrainingCenter::query()->findOrFail($approvedCenterId)
                    );
                }
            }

            $row->update([
                'status' => $data->status,
                'decision_notes' => $data->notes,
                'review_notes' => $data->notes,
                'reviewed_by_user_id' => $reviewer->id,
                'reviewed_at' => now(),
                'approved_training_center_id' => $approvedCenterId,
                'approved_at' => now(),
            ]);

            $this->auditLog->log('center_request_approved', $reviewer, $row, null, [
                'approved_training_center_id' => $approvedCenterId,
            ]);
        });

        return $row->refresh();
    }

    public function rejectCenterRequest(
        TrainingCenterRegistrationRequest $row,
        ReviewRegistrationRequestData $data,
        User $reviewer
    ): TrainingCenterRegistrationRequest {
        if ($row->isApproved()) {
            throw ValidationException::withMessages([
                'status' => ['لا يمكن رفض طلب معتمد مسبقاً.'],
            ]);
        }

        $row->update([
            'status' => $data->status,
            'decision_notes' => $data->notes,
            'review_notes' => $data->notes,
            'reviewed_by_user_id' => $reviewer->id,
            'reviewed_at' => now(),
            'rejected_at' => now(),
        ]);

        $this->auditLog->log('center_request_rejected', $reviewer, $row);

        return $row->refresh();
    }

    public function approveTrainerRequest(
        TrainerRegistrationRequest $row,
        ReviewRegistrationRequestData $data,
        User $reviewer
    ): TrainerRegistrationRequest {
        if ($row->isApproved() && $row->approved_trainer_id) {
            throw ValidationException::withMessages([
                'status' => ['تم اعتماد هذا الطلب مسبقاً ومرتبط بمدرب.'],
            ]);
        }

        if (!$row->isPending()) {
            throw ValidationException::withMessages([
                'status' => ['تمت معالجة هذا الطلب مسبقاً.'],
            ]);
        }

        DB::transaction(function () use ($row, $data, $reviewer) {
            $approvedTrainerId = $row->approved_trainer_id;

            if ($approvedTrainerId) {
                $trainer = Trainer::query()->findOrFail($approvedTrainerId);
            } else {
                $trainer = Trainer::create(array_merge([
                    'training_center_id' => $row->training_center_id,
                    'name' => $row->full_name,
                    'trainer_code' => $this->codeGenerator->nextTrainerCode(),
                    'phone' => $row->phone,
                    'email' => $row->email,
                    'specialization' => $row->specialization,
                    'classification' => $row->classification_requested,
                    'has_tot' => (bool) $row->has_tot,
                    'tot_certificate_number' => $row->tot_certificate_number,
                    'tot_certificate_source' => $row->tot_certificate_source,
                    'tot_issue_date' => $row->tot_issue_date,
                    'tot_expiry_date' => $row->tot_expiry_date,
                    'can_train' => true,
                    'can_evaluate' => false,
                    'status' => 'active',
                    'accreditation_start_date' => now()->toDateString(),
                    'accreditation_end_date' => now()->addYear()->toDateString(),
                    'notes' => 'Created from registration request #' . $row->request_number,
                ], $this->scopeFromRegistrationRow($row, $row->training_center_id)));

                $approvedTrainerId = $trainer->id;
            }

            if ($row->submitted_by_user_id) {
                $submitter = User::query()->find($row->submitted_by_user_id);
                if ($submitter) {
                    RegistrationApprovalLinker::linkUserToTrainer(
                        $submitter,
                        Trainer::query()->findOrFail($approvedTrainerId)
                    );
                }
            }

            $row->update([
                'status' => $data->status,
                'review_notes' => $data->notes,
                'reviewed_by_user_id' => $reviewer->id,
                'approved_trainer_id' => $approvedTrainerId,
                'approved_at' => now(),
            ]);

            $this->auditLog->log('trainer_request_approved', $reviewer, $row, null, [
                'approved_trainer_id' => $approvedTrainerId,
            ]);
        });

        return $row->refresh();
    }

    public function approveTraineeRequest(
        TraineeRegistrationRequest $row,
        ReviewRegistrationRequestData $data,
        User $reviewer
    ): TraineeRegistrationRequest {
        if ($row->isApproved() && $row->approved_trainee_id) {
            throw ValidationException::withMessages([
                'status' => ['تم اعتماد هذا الطلب مسبقاً ومرتبط بمتدرب.'],
            ]);
        }

        if (!$row->isPending()) {
            throw ValidationException::withMessages([
                'status' => ['تمت معالجة هذا الطلب مسبقاً.'],
            ]);
        }

        DB::transaction(function () use ($row, $data, $reviewer) {
            $approvedTraineeId = $row->approved_trainee_id;

            if ($approvedTraineeId) {
                $trainee = Trainee::query()->findOrFail($approvedTraineeId);
            } else {
                $trainee = Trainee::create(array_merge([
                    'name' => $row->full_name,
                    'trainee_code' => $this->codeGenerator->nextTraineeCode(),
                    'national_id' => $row->national_id,
                    'phone' => $row->phone,
                    'email' => $row->email,
                    'city' => $row->city,
                    'address' => $row->address,
                    'birth_date' => $row->birth_date,
                    'gender' => $row->gender,
                    'education_level' => $row->education_level,
                    'status' => 'active',
                    'notes' => 'Created from registration request #' . $row->request_number,
                ], $this->scopeFromRegistrationRow($row)));

                $approvedTraineeId = $trainee->id;
            }

            if ($row->submitted_by_user_id) {
                $submitter = User::query()->find($row->submitted_by_user_id);
                if ($submitter) {
                    RegistrationApprovalLinker::linkUserToTrainee(
                        $submitter,
                        Trainee::query()->findOrFail($approvedTraineeId)
                    );
                }
            }

            $row->update([
                'status' => $data->status,
                'review_notes' => $data->notes,
                'reviewed_by_user_id' => $reviewer->id,
                'approved_trainee_id' => $approvedTraineeId,
                'approved_at' => now(),
            ]);

            $this->auditLog->log('trainee_request_approved', $reviewer, $row, null, [
                'approved_trainee_id' => $approvedTraineeId,
            ]);
        });

        return $row->refresh();
    }

    /** @return array{branch_id: int|null, governorate_id: int|null} */
    private function scopeFromRegistrationRow(object $row, int|string|null $centerId = null): array
    {
        if (!empty($row->branch_id) || !empty($row->governorate_id)) {
            return [
                'branch_id' => $row->branch_id,
                'governorate_id' => $row->governorate_id,
            ];
        }

        $resolvedCenterId = $centerId ?? ($row->training_center_id ?? null);

        return RegistrationBranchResolver::fromTrainingCenter($resolvedCenterId);
    }

    private function resolveSupervisorId(?int $branchId, ?int $governorateId): int
    {
        return $this->supervisorResolver->resolveForScope($branchId, $governorateId);
    }
}
