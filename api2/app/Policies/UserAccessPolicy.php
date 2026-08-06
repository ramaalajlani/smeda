<?php

namespace App\Policies;

use App\Models\User;
use App\Support\AccessControlGuard;

class UserAccessPolicy
{
    private function canAdministerUsers(User $user): bool
    {
        if (AccessControlGuard::isAccessAdministrator($user)) {
            return true;
        }

        return $user->hasPermissionTo('manage_user_access')
            && (
                $user->hasRole('project_services_manager')
                || count(AccessControlGuard::getDelegatableRoles($user)) > 0
            );
    }

    public function viewAny(User $user): bool
    {
        return $this->canAdministerUsers($user)
            && $user->hasPermissionTo('view_users');
    }

    public function viewAccess(User $user, User $target): bool
    {
        return $this->viewAny($user)
            && AccessControlGuard::canActorManageTarget($user, $target);
    }

    public function assignRole(User $user, User $target): bool
    {
        return $this->canAdministerUsers($user)
            && $user->hasPermissionTo('assign_roles')
            && (int) $user->id !== (int) $target->id
            && AccessControlGuard::canActorManageTarget($user, $target);
    }

    public function revokeRole(User $user, User $target): bool
    {
        return $this->canAdministerUsers($user)
            && $user->hasPermissionTo('revoke_roles')
            && (int) $user->id !== (int) $target->id
            && AccessControlGuard::canActorManageTarget($user, $target);
    }

    public function assignPermission(User $user, User $target): bool
    {
        if ((int) $user->id === (int) $target->id) {
            return false;
        }

        if (AccessControlGuard::isAccessAdministrator($user)) {
            return $user->hasPermissionTo('assign_permissions')
                && AccessControlGuard::canActorManageTarget($user, $target);
        }

        // الأب (مثل مدير خدمات المشروعات) يمنح صلاحيات يملكها فقط لأبنائه
        return AccessControlGuard::canActorManageTarget($user, $target)
            && count(AccessControlGuard::getDelegatableRoles($user)) > 0;
    }

    public function revokePermission(User $user, User $target): bool
    {
        if ((int) $user->id === (int) $target->id) {
            return false;
        }

        if (AccessControlGuard::isAccessAdministrator($user)) {
            return $user->hasPermissionTo('revoke_permissions')
                && AccessControlGuard::canActorManageTarget($user, $target);
        }

        return AccessControlGuard::canActorManageTarget($user, $target)
            && count(AccessControlGuard::getDelegatableRoles($user)) > 0;
    }

    public function manageAccess(User $user): bool
    {
        return $this->canAdministerUsers($user)
            && $user->hasPermissionTo('manage_user_access');
    }

    public function updateStatus(User $user, User $target): bool
    {
        return $this->manageAccess($user)
            && (int) $user->id !== (int) $target->id
            && AccessControlGuard::canActorManageTarget($user, $target);
    }

    public function create(User $user): bool
    {
        return $this->manageAccess($user)
            || (
                ! AccessControlGuard::isNationalAdministrator($user)
                && count(AccessControlGuard::getDelegatableRoles($user)) > 0
            );
    }

    public function update(User $user, User $target): bool
    {
        return $this->manageAccess($user)
            && (int) $user->id !== (int) $target->id
            && AccessControlGuard::canActorManageTarget($user, $target);
    }

    public function changePassword(User $user, User $target): bool
    {
        return $this->manageAccess($user)
            && (int) $user->id !== (int) $target->id
            && AccessControlGuard::canActorManageTarget($user, $target);
    }
}
