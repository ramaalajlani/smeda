<?php

namespace App\Policies;

use App\Models\User;
use App\Support\AccessControlGuard;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return AccessControlGuard::isAccessAdministrator($user)
            && ($user->hasPermissionTo('view_roles') || $user->hasPermissionTo('manage_roles'));
    }

    public function view(User $user, Role $role): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return AccessControlGuard::isAccessAdministrator($user)
            && ($user->hasPermissionTo('create_roles') || $user->hasPermissionTo('manage_roles'));
    }

    public function update(User $user, Role $role): bool
    {
        return AccessControlGuard::isAccessAdministrator($user)
            && ($user->hasPermissionTo('update_roles') || $user->hasPermissionTo('manage_roles'));
    }

    public function delete(User $user, Role $role): bool
    {
        return AccessControlGuard::isAccessAdministrator($user)
            && ($user->hasPermissionTo('delete_roles') || $user->hasPermissionTo('manage_roles'));
    }

    public function syncPermissions(User $user, Role $role): bool
    {
        return AccessControlGuard::isAccessAdministrator($user)
            && ($user->hasPermissionTo('manage_roles') || $user->hasPermissionTo('update_roles'));
    }
}
