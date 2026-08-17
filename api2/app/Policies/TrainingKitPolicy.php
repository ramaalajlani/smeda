<?php

namespace App\Policies;

use App\Models\TrainingKit;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Policies\Concerns\TrainingPolicyHelpers;

class TrainingKitPolicy
{
    use GrantsPlatformAdminFullAccess;
    use TrainingPolicyHelpers;

    public function viewAny(?User $user): bool
    {
        return $this->hasPermission($user, 'view_kits');
    }

    public function view(?User $user, TrainingKit $kit): bool
    {
        return $this->hasPermission($user, 'view_kits');
    }

    public function create(?User $user): bool
    {
        return $this->hasPermission($user, 'manage_kits');
    }

    public function update(?User $user, TrainingKit $kit): bool
    {
        return $this->hasPermission($user, 'manage_kits');
    }

    public function downloadPromotionalFile(?User $user, TrainingKit $kit): bool
    {
        return $this->hasPermission($user, 'view_kits') && $kit->hasPromotionalFile();
    }

    public function downloadTrainingBagFile(?User $user, TrainingKit $kit): bool
    {
        return $this->hasPermission($user, 'manage_kits') && $kit->hasTrainingBagFile();
    }

    public function publish(?User $user, TrainingKit $kit): bool
    {
        return $this->hasPermission($user, 'manage_kits');
    }
}
