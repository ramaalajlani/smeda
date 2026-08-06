<?php

namespace App\Policies;

use App\Models\TrainerProfile;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Policies\Concerns\TrainingPolicyHelpers;
use App\Support\TrainingDataScope;

class TrainerProfilePolicy
{
    use GrantsPlatformAdminFullAccess;
    use TrainingPolicyHelpers;

    public function view(?User $user, TrainerProfile $profile): bool
    {
        if ($this->hasPermission($user, 'view_trainer_profiles')) {
            if ($this->hasBroadRead($user)) {
                return true;
            }

            if ($user?->trainer_id && (int) $profile->trainer_id === (int) $user->trainer_id) {
                return true;
            }

            return TrainingDataScope::scopeTrainers(
                \App\Models\Trainer::query()->whereKey($profile->trainer_id),
                $user
            )->exists();
        }

        return $this->hasPermission($user, 'edit_own_trainer_profile')
            && $user?->trainer_id
            && (int) $profile->trainer_id === (int) $user->trainer_id;
    }

    public function updateOwn(?User $user): bool
    {
        return $this->hasPermission($user, 'edit_own_trainer_profile')
            && $user?->trainer_id !== null;
    }
}
