<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;
use App\Support\AccessControlGuard;

class BranchPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user && ($user->can('view_branches') || $user->hasRole('branch_manager'));
    }

    public function view(?User $user, Branch $branch): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return $user && AccessControlGuard::isNationalAdministrator($user)
            && $user->hasPermissionTo('manage_branches');
    }

    public function update(?User $user, Branch $branch): bool
    {
        return $this->create($user);
    }

    public function delete(?User $user, Branch $branch): bool
    {
        return $this->create($user);
    }
}
