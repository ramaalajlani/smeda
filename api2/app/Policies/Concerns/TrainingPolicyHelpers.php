<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Support\TrainingDataScope;

trait TrainingPolicyHelpers
{
    protected function hasPermission(?User $user, string $permission): bool
    {
        return $user !== null
            && method_exists($user, 'hasPermissionTo')
            && $user->hasPermissionTo($permission);
    }

    protected function hasBroadRead(?User $user): bool
    {
        return TrainingDataScope::hasBroadTrainingReadAccess($user);
    }

    protected function hasUnrestricted(?User $user): bool
    {
        return TrainingDataScope::hasUnrestrictedTrainingAccess($user);
    }
}
