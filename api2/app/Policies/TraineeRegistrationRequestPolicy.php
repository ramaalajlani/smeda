<?php

namespace App\Policies;

use App\Models\TraineeRegistrationRequest;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Policies\Concerns\RegistrationRequestPolicyHelpers;
use App\Policies\Concerns\TrainingPolicyHelpers;

class TraineeRegistrationRequestPolicy
{
    use GrantsPlatformAdminFullAccess;
    use RegistrationRequestPolicyHelpers;
    use TrainingPolicyHelpers;

    public function viewAny(?User $user): bool
    {
        return $this->hasPermission($user, 'view_registration_requests');
    }

    public function view(?User $user, TraineeRegistrationRequest $request): bool
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
            $request->submitted_by_user_id
        );
    }

    public function create(?User $user): bool
    {
        return $this->hasPermission($user, 'create_trainee_registration_requests');
    }

    public function review(?User $user, TraineeRegistrationRequest $request): bool
    {
        return $this->hasPermission($user, 'review_trainee_registration_requests')
            && $this->canAccessRegistrationRequest(
                $user,
                $request->branch_id,
                $request->submitted_by_user_id
            );
    }
}
