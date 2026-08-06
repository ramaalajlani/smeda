<?php

namespace App\Support;

use App\Models\Incubator;
use App\Models\User;

class IncubationAccess
{
    /** @var list<string> */
    public const VIEW_ROLES = [
        'general_director',
        'deputy_general_director',
        'branch_manager',
        'branch_officer',
        'incubator_manager',
        'incubator_mentor',
        'admin',
        'super_admin',
        'system_admin',
        'auditor',
    ];

    /** @var list<string> */
    public const MANAGE_ROLES = [
        'general_director',
        'admin',
        'super_admin',
        'system_admin',
        'incubator_manager',
    ];

    public static function hasViewAccess(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasRole(self::VIEW_ROLES)
            || $user->hasPermissionTo('incubation.view');
    }

    public static function hasManageAccess(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasRole(self::MANAGE_ROLES)
            || $user->hasPermissionTo('incubation.manage');
    }

    public static function isIncubatorManager(?User $user, ?Incubator $incubator): bool
    {
        if (!$user || !$incubator) {
            return false;
        }

        return (int) $incubator->manager_user_id === (int) $user->id;
    }

    public static function sharesBranch(?User $user, ?Incubator $incubator): bool
    {
        if (!$user || !$incubator || !$user->branch_id || !$incubator->branch_id) {
            return false;
        }

        return (int) $user->branch_id === (int) $incubator->branch_id;
    }
}
