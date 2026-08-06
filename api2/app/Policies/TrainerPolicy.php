<?php

namespace App\Policies;

use App\Models\Trainer;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Policies\Concerns\TrainingPolicyHelpers;
use App\Support\TrainingDataScope;

class TrainerPolicy
{
    use GrantsPlatformAdminFullAccess;
    use TrainingPolicyHelpers;

    public function viewAny(?User $user): bool
    {
        return $this->hasPermission($user, 'view_trainers');
    }

    public function view(?User $user, Trainer $trainer): bool
    {
        if (!$this->hasPermission($user, 'view_trainers')) {
            return false;
        }

        return TrainingDataScope::scopeTrainers(Trainer::query()->whereKey($trainer->id), $user)->exists();
    }

    public function printCard(?User $user, Trainer $trainer): bool
    {
        return $this->hasPermission($user, 'print_certificates')
            && $this->view($user, $trainer);
    }

    public function create(?User $user): bool
    {
        return $this->hasPermission($user, 'manage_trainers');
    }

    public function update(?User $user, Trainer $trainer): bool
    {
        if (!$this->hasPermission($user, 'manage_trainers')) {
            return false;
        }

        return TrainingDataScope::scopeTrainers(Trainer::query()->whereKey($trainer->id), $user)->exists();
    }
}
