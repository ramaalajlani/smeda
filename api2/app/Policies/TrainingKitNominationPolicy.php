<?php

namespace App\Policies;

use App\Models\TrainingKitNomination;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Policies\Concerns\TrainingPolicyHelpers;
use App\Support\TrainingDataScope;

class TrainingKitNominationPolicy
{
    use GrantsPlatformAdminFullAccess;
    use TrainingPolicyHelpers;

    public function viewAny(?User $user): bool
    {
        return $this->hasPermission($user, 'nominate_training_kits')
            || $this->hasPermission($user, 'review_training_kit_nominations');
    }

    public function view(?User $user, TrainingKitNomination $nomination): bool
    {
        if (!$this->viewAny($user)) {
            return false;
        }

        if ($this->hasPermission($user, 'review_training_kit_nominations')) {
            return true;
        }

        return $user?->trainer_id
            && (int) $nomination->trainer_id === (int) $user->trainer_id;
    }

    public function create(?User $user): bool
    {
        return $this->hasPermission($user, 'nominate_training_kits')
            && $user?->trainer_id !== null;
    }

    public function review(?User $user, TrainingKitNomination $nomination): bool
    {
        return $this->hasPermission($user, 'review_training_kit_nominations');
    }
}
