<?php

namespace App\Policies;

use App\Models\CourseRegistrationRequest;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Policies\Concerns\RegistrationRequestPolicyHelpers;
use App\Policies\Concerns\TrainingPolicyHelpers;

class CourseRegistrationRequestPolicy
{
    use GrantsPlatformAdminFullAccess;
    use RegistrationRequestPolicyHelpers;
    use TrainingPolicyHelpers;

    public function viewAny(?User $user): bool
    {
        return $this->hasPermission($user, 'view_registration_requests');
    }

    public function view(?User $user, CourseRegistrationRequest $request): bool
    {
        if (!$user) {
            return false;
        }

        if ((int) $request->submitted_by_user_id === (int) $user->id) {
            return true;
        }

        if (!$this->viewAny($user)) {
            return false;
        }

        return $this->canAccessRegistrationRequest(
            $user,
            $request->branch_id,
            $request->submitted_by_user_id,
            null,
            $request->training_course_id
        );
    }

    public function create(?User $user): bool
    {
        return $this->hasPermission($user, 'create_course_registration_requests');
    }

    public function confirm(?User $user, CourseRegistrationRequest $request): bool
    {
        return $this->hasPermission($user, 'confirm_course_registration_requests')
            && ((int) $request->submitted_by_user_id === (int) $user?->id || $this->hasBroadRead($user));
    }

    public function complete(?User $user, CourseRegistrationRequest $request): bool
    {
        if (!$this->hasPermission($user, 'complete_course_registration_requests')) {
            return false;
        }

        if ($this->hasUnrestricted($user)) {
            return true;
        }

        $request->loadMissing('trainingCourse');

        return $user?->isCenterUser()
            && $user->training_center_id
            && $request->trainingCourse
            && (int) $request->trainingCourse->training_center_id === (int) $user->training_center_id;
    }

    public function cancel(?User $user, CourseRegistrationRequest $request): bool
    {
        if ((int) $request->submitted_by_user_id === (int) $user?->id) {
            return true;
        }

        return $this->hasBroadRead($user);
    }
}
