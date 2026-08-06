<?php

namespace App\Policies;

use App\Models\Trainee;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Policies\Concerns\TrainingPolicyHelpers;
use App\Support\TrainingDataScope;

class TraineePolicy
{
    use GrantsPlatformAdminFullAccess;
    use TrainingPolicyHelpers;

    public function viewAny(?User $user): bool
    {
        return $this->hasPermission($user, 'view_trainees');
    }

    public function view(?User $user, Trainee $trainee): bool
    {
        if (!$this->hasPermission($user, 'view_trainees')) {
            return false;
        }

        return TrainingDataScope::scopeTrainees(Trainee::query()->whereKey($trainee->id), $user)->exists();
    }

    public function printCard(?User $user, Trainee $trainee): bool
    {
        return $this->hasPermission($user, 'print_certificates')
            && $this->view($user, $trainee);
    }

    public function create(?User $user): bool
    {
        return $this->hasPermission($user, 'manage_trainees');
    }

    public function update(?User $user, Trainee $trainee): bool
    {
        if (!$this->hasPermission($user, 'manage_trainees')) {
            return false;
        }

        return TrainingDataScope::scopeTrainees(Trainee::query()->whereKey($trainee->id), $user)->exists();
    }
}
