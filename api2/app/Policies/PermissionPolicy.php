<?php

namespace App\Policies;

use App\Models\User;
use App\Support\AccessControlGuard;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return AccessControlGuard::isAccessAdministrator($user)
            && ($user->hasPermissionTo('view_permissions') || $user->hasPermissionTo('manage_permissions'));
    }

    public function view(User $user, Permission $permission): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return AccessControlGuard::isAccessAdministrator($user)
            && ($user->hasPermissionTo('create_permissions') || $user->hasPermissionTo('manage_permissions'));
    }

    public function update(User $user, Permission $permission): bool
    {
        return AccessControlGuard::isAccessAdministrator($user)
            && ($user->hasPermissionTo('update_permissions') || $user->hasPermissionTo('manage_permissions'));
    }

    public function delete(User $user, Permission $permission): bool
    {
        return AccessControlGuard::isAccessAdministrator($user)
            && ($user->hasPermissionTo('delete_permissions') || $user->hasPermissionTo('manage_permissions'));
    }
}
