<?php

namespace App\Policies;

use App\Models\TrainingCourse;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Policies\Concerns\TrainingPolicyHelpers;
use App\Support\TrainingDataScope;

class TrainingCoursePolicy
{
    use GrantsPlatformAdminFullAccess;
    use TrainingPolicyHelpers;

    public function viewAny(?User $user): bool
    {
        return $this->hasPermission($user, 'view_courses')
            || $this->hasPermission($user, 'view_course_details');
    }

    public function view(?User $user, TrainingCourse $course): bool
    {
        if (!$this->viewAny($user)) {
            return false;
        }

        return TrainingDataScope::scopeTrainingCourses(
            TrainingCourse::query()->whereKey($course->id),
            $user
        )->exists();
    }

    public function create(?User $user): bool
    {
        return $this->hasPermission($user, 'manage_courses');
    }

    public function update(?User $user, TrainingCourse $course): bool
    {
        return $this->hasPermission($user, 'manage_courses')
            && TrainingDataScope::canManageTrainingCourse($user, $course);
    }

    public function complete(?User $user, TrainingCourse $course): bool
    {
        return $this->update($user, $course);
    }

    public function manageTrainees(?User $user, TrainingCourse $course): bool
    {
        return $this->hasPermission($user, 'manage_courses')
            && TrainingDataScope::canManageTrainingCourse($user, $course);
    }

    public function updateTraineeResult(?User $user, TrainingCourse $course): bool
    {
        return $this->hasPermission($user, 'manage_courses')
            && TrainingDataScope::canUpdateTraineeResults($user, $course);
    }

    public function deleteTrainee(?User $user, TrainingCourse $course): bool
    {
        return $this->manageTrainees($user, $course);
    }
}
