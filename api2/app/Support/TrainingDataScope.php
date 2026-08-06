<?php

namespace App\Support;

use App\Models\Certificate;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\User;
use App\Support\AccessControlGuard;
use App\Support\BranchDataScope;
use Illuminate\Database\Eloquent\Builder;

class TrainingDataScope
{
    public static function hasUnrestrictedTrainingAccess(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return AccessControlGuard::isNationalAdministrator($user)
            || $user->isTrainingManager()
            || $user->hasRole('project_services_manager');
    }

    public static function hasBroadTrainingReadAccess(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return BranchDataScope::hasNationalReadAccess($user);
    }

    private static function applyBranchScopeFirst(Builder $query, ?User $user, string $column = 'branch_id'): ?Builder
    {
        if (!$user || BranchDataScope::hasNationalReadAccess($user)) {
            return null;
        }

        if (BranchDataScope::isBranchManager($user)) {
            return BranchDataScope::applyBranchScope($query, $user, $column);
        }

        return null;
    }

    public static function scopeTrainingCenters(Builder $query, ?User $user): Builder
    {
        $branchScoped = self::applyBranchScopeFirst($query, $user);
        if ($branchScoped) {
            return $branchScoped;
        }

        if (!$user || self::hasBroadTrainingReadAccess($user)) {
            return $query;
        }

        if ($user->isCenterUser() && $user->training_center_id) {
            return $query->whereKey($user->training_center_id);
        }

        if ($user->isTrainingSupervisor() && $user->training_supervisor_id) {
            return $query->where('supervisor_id', $user->training_supervisor_id);
        }

        if ($user->isTrainerUser() && $user->trainer_id) {
            $centerId = Trainer::query()->whereKey($user->trainer_id)->value('training_center_id');

            return $centerId
                ? $query->whereKey($centerId)
                : $query->whereRaw('0 = 1');
        }

        if ($user->isTraineeUser() && $user->trainee_id) {
            return $query->whereIn('id', function ($sub) use ($user) {
                $sub->select('training_center_id')
                    ->from('training_courses')
                    ->whereIn('id', function ($courseSub) use ($user) {
                        $courseSub->select('training_course_id')
                            ->from('training_course_trainee')
                            ->where('trainee_id', $user->trainee_id);
                    });
            });
        }

        return $query->whereRaw('0 = 1');
    }

    public static function scopeTrainers(Builder $query, ?User $user): Builder
    {
        $branchScoped = self::applyBranchScopeFirst($query, $user);
        if ($branchScoped) {
            return $branchScoped;
        }

        if (!$user || self::hasBroadTrainingReadAccess($user)) {
            return $query;
        }

        if ($user->isCenterUser() && $user->training_center_id) {
            return $query->where('training_center_id', $user->training_center_id);
        }

        if ($user->isTrainingSupervisor() && $user->training_supervisor_id) {
            return $query->whereHas(
                'trainingCenter',
                fn (Builder $center) => $center->where('supervisor_id', $user->training_supervisor_id)
            );
        }

        if ($user->isTrainerUser() && $user->trainer_id) {
            return $query->whereKey($user->trainer_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public static function scopeTrainees(Builder $query, ?User $user): Builder
    {
        $branchScoped = self::applyBranchScopeFirst($query, $user);
        if ($branchScoped) {
            return $branchScoped;
        }

        if (!$user || self::hasBroadTrainingReadAccess($user)) {
            return $query;
        }

        if ($user->isCenterUser() && $user->training_center_id) {
            $centerId = $user->training_center_id;
            $marker = '[center:' . $centerId . ']';

            return $query->where(function (Builder $scoped) use ($centerId, $marker) {
                $scoped->where('owned_training_center_id', $centerId)
                    ->orWhereHas('courses', fn (Builder $course) => $course->where('training_center_id', $centerId))
                    ->orWhereHas('certificates', fn (Builder $certificate) => $certificate->where('training_center_id', $centerId))
                    // توافق مؤقت مع بيانات قديمة بلا عمود مملوء
                    ->orWhere('notes', 'like', $marker . '%');
            });
        }

        if ($user->isTrainingSupervisor() && $user->training_supervisor_id) {
            return $query->whereHas(
                'courses',
                fn (Builder $course) => $course->whereHas(
                    'trainingCenter',
                    fn (Builder $center) => $center->where('supervisor_id', $user->training_supervisor_id)
                )
            );
        }

        if ($user->isTrainerUser() && $user->trainer_id) {
            return $query->whereHas(
                'courses',
                fn (Builder $course) => $course->where('trainer_id', $user->trainer_id)
            );
        }

        if ($user->isTraineeUser() && $user->trainee_id) {
            return $query->whereKey($user->trainee_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public static function scopeTrainingCourses(Builder $query, ?User $user): Builder
    {
        $branchScoped = self::applyBranchScopeFirst($query, $user);
        if ($branchScoped) {
            return $branchScoped;
        }

        if (!$user || self::hasBroadTrainingReadAccess($user)) {
            return $query;
        }

        if ($user->isCenterUser() && $user->training_center_id) {
            return $query->where('training_center_id', $user->training_center_id);
        }

        if ($user->isTrainingSupervisor() && $user->training_supervisor_id) {
            return $query->whereHas(
                'trainingCenter',
                fn (Builder $center) => $center->where('supervisor_id', $user->training_supervisor_id)
            );
        }

        if ($user->isTrainerUser() && $user->trainer_id) {
            return $query->where('trainer_id', $user->trainer_id);
        }

        if ($user->isTraineeUser() && $user->trainee_id) {
            return $query->whereHas(
                'trainees',
                fn (Builder $trainee) => $trainee->where('trainees.id', $user->trainee_id)
            );
        }

        return $query->whereRaw('0 = 1');
    }

    public static function scopeCertificates(Builder $query, ?User $user): Builder
    {
        $branchScoped = self::applyBranchScopeFirst($query, $user);
        if ($branchScoped) {
            return $branchScoped;
        }

        if (!$user || self::hasBroadTrainingReadAccess($user)) {
            return $query;
        }

        if ($user->isCenterUser() && $user->training_center_id) {
            return $query->where('training_center_id', $user->training_center_id);
        }

        if ($user->isTrainingSupervisor() && $user->training_supervisor_id) {
            return $query->whereHas(
                'trainingCenter',
                fn (Builder $center) => $center->where('supervisor_id', $user->training_supervisor_id)
            );
        }

        if ($user->isTrainerUser() && $user->trainer_id) {
            return $query->where('trainer_id', $user->trainer_id);
        }

        if ($user->isTraineeUser() && $user->trainee_id) {
            return $query->where('trainee_id', $user->trainee_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public static function scopeTrainingKits(Builder $query, ?User $user): Builder
    {
        if (!$user || self::hasBroadTrainingReadAccess($user)) {
            return $query;
        }

        if (BranchDataScope::isBranchManager($user)) {
            if (!$user->branch_id) {
                return $query->whereRaw('0 = 1');
            }

            return $query->where(function (Builder $scoped) use ($user) {
                $scoped->whereHas(
                    'centers',
                    fn (Builder $center) => $center->where('branch_id', $user->branch_id)
                )->orWhereHas(
                    'courses',
                    fn (Builder $course) => $course->where('branch_id', $user->branch_id)
                );
            });
        }

        if ($user->isCenterUser() && $user->training_center_id) {
            $centerId = (int) $user->training_center_id;

            return $query->where(function (Builder $scoped) use ($centerId) {
                $scoped->whereHas(
                    'centers',
                    fn (Builder $center) => $center->whereKey($centerId)
                )->orWhereHas(
                    'courses',
                    fn (Builder $course) => $course->where('training_center_id', $centerId)
                )->orWhereHas(
                    'trainers',
                    fn (Builder $trainer) => $trainer->where('training_center_id', $centerId)
                );
            });
        }

        if ($user->isTrainerUser() && $user->trainer_id) {
            return $query->whereHas(
                'trainers',
                fn (Builder $trainer) => $trainer->whereKey($user->trainer_id)
            );
        }

        return $query->whereRaw('0 = 1');
    }

    public static function scopeKitNominations(Builder $query, ?User $user): Builder
    {
        if (!$user || self::hasBroadTrainingReadAccess($user)) {
            return $query;
        }

        if (BranchDataScope::isBranchManager($user)) {
            if (!$user->branch_id) {
                return $query->whereRaw('0 = 1');
            }

            return $query->whereHas(
                'trainer',
                fn (Builder $trainer) => $trainer->where('branch_id', $user->branch_id)
            );
        }

        if ($user->isCenterUser() && $user->training_center_id) {
            return $query->whereHas(
                'trainer',
                fn (Builder $trainer) => $trainer->where('training_center_id', $user->training_center_id)
            );
        }

        if ($user->isTrainingSupervisor() && $user->training_supervisor_id) {
            return $query->whereHas(
                'trainer.trainingCenter',
                fn (Builder $center) => $center->where('supervisor_id', $user->training_supervisor_id)
            );
        }

        if ($user->isTrainerUser() && $user->trainer_id) {
            return $query->where('trainer_id', $user->trainer_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public static function canManageTrainingCourse(?User $user, TrainingCourse $course): bool
    {
        if (!$user) {
            return false;
        }

        if (self::hasUnrestrictedTrainingAccess($user)) {
            return true;
        }

        if ($user->isCenterUser() && $user->training_center_id) {
            return (int) $course->training_center_id === (int) $user->training_center_id;
        }

        if (BranchDataScope::isBranchManager($user) && $user->branch_id) {
            return (int) $course->branch_id === (int) $user->branch_id;
        }

        return false;
    }

    public static function canUpdateTraineeResults(?User $user, TrainingCourse $course): bool
    {
        return self::canManageTrainingCourse($user, $course);
    }

    public static function canAccessTrainingCourse(?User $user, int|string|null $centerId, int|string|null $trainerId): bool
    {
        if (!$user) {
            return false;
        }

        if (self::hasBroadTrainingReadAccess($user)) {
            return true;
        }

        if ($user->isCenterUser()) {
            return $user->belongsToCenter($centerId);
        }

        if ($user->isTrainerUser()) {
            return $user->belongsToTrainer($trainerId);
        }

        return false;
    }

    public static function canAccessCertificate(?User $user, Certificate $certificate): bool
    {
        if (!$user) {
            return false;
        }

        if (self::hasBroadTrainingReadAccess($user)) {
            return true;
        }

        if ($user->isCenterUser()) {
            return $user->belongsToCenter($certificate->training_center_id);
        }

        if ($user->isTrainerUser()) {
            return $user->belongsToTrainer($certificate->trainer_id);
        }

        if ($user->isTraineeUser()) {
            return $user->belongsToTrainee($certificate->trainee_id);
        }

        return false;
    }

    public static function canViewTraineeSensitive(?User $user, int|string|null $traineeId): bool
    {
        if (!$user || blank($traineeId)) {
            return false;
        }

        if (self::hasBroadTrainingReadAccess($user)) {
            return true;
        }

        if ($user->isTraineeUser()) {
            return $user->belongsToTrainee($traineeId);
        }

        return Trainee::query()
            ->whereKey($traineeId)
            ->tap(fn (Builder $scoped) => self::scopeTrainees($scoped, $user))
            ->exists();
    }

    public static function canViewTrainerContact(?User $user, int|string|null $trainerId, int|string|null $centerId): bool
    {
        if (!$user) {
            return false;
        }

        if (self::hasBroadTrainingReadAccess($user)) {
            return true;
        }

        if ($user->isCenterUser()) {
            return $user->belongsToCenter($centerId);
        }

        if ($user->isTrainerUser()) {
            return $user->belongsToTrainer($trainerId);
        }

        return false;
    }

    public static function canViewCertificateSecrets(?User $user, Certificate $certificate): bool
    {
        if (!$user) {
            return false;
        }

        if (self::hasUnrestrictedTrainingAccess($user)) {
            return true;
        }

        if ($user->isCenterUser()) {
            return $user->belongsToCenter($certificate->training_center_id);
        }

        if ($user->isTrainerUser()) {
            return $user->belongsToTrainer($certificate->trainer_id);
        }

        return false;
    }

    public static function scopeSubmittedRegistrationRequests(Builder $query, ?User $user): Builder
    {
        if (!$user || self::hasBroadTrainingReadAccess($user)) {
            return $query;
        }

        if ($branchScoped = self::applyBranchScopeFirst($query, $user)) {
            return $branchScoped;
        }

        return $query->where('submitted_by_user_id', $user->id);
    }

    public static function scopeTrainerRegistrationRequests(Builder $query, ?User $user): Builder
    {
        if (!$user || self::hasBroadTrainingReadAccess($user)) {
            return $query;
        }

        if ($branchScoped = self::applyBranchScopeFirst($query, $user)) {
            return $branchScoped;
        }

        if ($user->isCenterUser() && $user->training_center_id) {
            return $query->where(function (Builder $scoped) use ($user) {
                $scoped->where('training_center_id', $user->training_center_id)
                    ->orWhere('submitted_by_user_id', $user->id);
            });
        }

        return $query->where('submitted_by_user_id', $user->id);
    }

    public static function scopeCourseRegistrationRequests(Builder $query, ?User $user): Builder
    {
        if (!$user || self::hasBroadTrainingReadAccess($user)) {
            return $query;
        }

        if ($branchScoped = self::applyBranchScopeFirst($query, $user)) {
            return $branchScoped;
        }

        if ($user->isCenterUser() && $user->training_center_id) {
            $centerId = $user->training_center_id;

            return $query->where(function (Builder $scoped) use ($centerId, $user) {
                $scoped->where('submitted_by_user_id', $user->id)
                    ->orWhereHas(
                        'trainingCourse',
                        fn (Builder $course) => $course->where('training_center_id', $centerId)
                    );
            });
        }

        return $query->where('submitted_by_user_id', $user->id);
    }

    public static function canAccessRegistrationRequest(
        ?User $user,
        ?int $branchId,
        ?int $submittedByUserId,
        ?int $trainingCenterId = null,
        ?int $trainingCourseId = null,
    ): bool {
        if (!$user) {
            return false;
        }

        if (self::hasBroadTrainingReadAccess($user)) {
            return true;
        }

        if (BranchDataScope::isBranchManager($user) && $user->branch_id) {
            if ($branchId) {
                return (int) $branchId === (int) $user->branch_id;
            }

            if ($trainingCenterId) {
                return TrainingCenter::query()
                    ->whereKey($trainingCenterId)
                    ->where('branch_id', $user->branch_id)
                    ->exists();
            }

            if ($trainingCourseId) {
                return TrainingCourse::query()
                    ->whereKey($trainingCourseId)
                    ->where('branch_id', $user->branch_id)
                    ->exists();
            }

            if ($submittedByUserId) {
                return User::query()
                    ->whereKey($submittedByUserId)
                    ->where('branch_id', $user->branch_id)
                    ->exists();
            }

            return false;
        }

        if ($user->isCenterUser() && $user->training_center_id) {
            if ($trainingCenterId) {
                return (int) $trainingCenterId === (int) $user->training_center_id
                    || (int) $submittedByUserId === (int) $user->id;
            }

            if ($trainingCourseId) {
                return TrainingCourse::query()
                    ->whereKey($trainingCourseId)
                    ->where('training_center_id', $user->training_center_id)
                    ->exists()
                    || (int) $submittedByUserId === (int) $user->id;
            }
        }

        return (int) $submittedByUserId === (int) $user->id;
    }
}
