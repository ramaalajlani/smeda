<?php

namespace App\Policies;

use App\Models\IncubationApplication;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Support\IncubationAccess;

class IncubationApplicationPolicy
{
    use GrantsPlatformAdminFullAccess;

    public function viewAny(?User $user): bool
    {
        return IncubationAccess::hasViewAccess($user)
            || IncubationAccess::hasManageAccess($user);
    }

    public function view(?User $user, IncubationApplication $application): bool
    {
        if (!$user) {
            return false;
        }

        if ((int) $application->applicant_user_id === (int) $user->id) {
            return true;
        }

        if (IncubationAccess::hasManageAccess($user)) {
            return true;
        }

        if (!IncubationAccess::hasViewAccess($user)) {
            return false;
        }

        $application->loadMissing('incubator', 'project');
        $incubator = $application->incubator;

        if (IncubationAccess::isIncubatorManager($user, $incubator)) {
            return true;
        }

        if ($application->project && (int) $application->project->mentor_user_id === (int) $user->id) {
            return true;
        }

        if ($user->hasRole(['branch_manager', 'branch_officer']) && IncubationAccess::sharesBranch($user, $incubator)) {
            return true;
        }

        if ($user->hasRole(['incubator_mentor', 'auditor', 'deputy_general_director'])) {
            return true;
        }

        return false;
    }

    public function viewSensitive(?User $user, IncubationApplication $application): bool
    {
        if (!$user) {
            return false;
        }

        if ((int) $application->applicant_user_id === (int) $user->id) {
            return true;
        }

        if (IncubationAccess::hasManageAccess($user)) {
            return true;
        }

        $application->loadMissing('incubator', 'project');
        $incubator = $application->incubator;

        if (IncubationAccess::isIncubatorManager($user, $incubator)) {
            return true;
        }

        if ($application->project && (int) $application->project->mentor_user_id === (int) $user->id) {
            return true;
        }

        if ($user->hasRole('branch_manager') && IncubationAccess::sharesBranch($user, $incubator)) {
            return true;
        }

        return false;
    }

    public function review(?User $user, IncubationApplication $application): bool
    {
        return IncubationAccess::hasManageAccess($user)
            || IncubationAccess::isIncubatorManager($user, $application->incubator);
    }

    public function create(?User $user): bool
    {
        return (bool) $user;
    }
}
