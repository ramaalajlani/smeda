<?php

namespace App\Policies;

use App\Models\TrainingCategory;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Policies\Concerns\TrainingPolicyHelpers;

class TrainingCategoryPolicy
{
    use GrantsPlatformAdminFullAccess;
    use TrainingPolicyHelpers;

    public function viewAny(?User $user): bool
    {
        return $this->hasPermission($user, 'view_kits')
            || $this->hasPermission($user, 'manage_training_categories');
    }

    public function view(?User $user, TrainingCategory $category): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return $this->hasPermission($user, 'manage_training_categories');
    }

    public function update(?User $user, TrainingCategory $category): bool
    {
        return $this->hasPermission($user, 'manage_training_categories');
    }

    public function delete(?User $user, TrainingCategory $category): bool
    {
        return $this->hasPermission($user, 'manage_training_categories');
    }
}
