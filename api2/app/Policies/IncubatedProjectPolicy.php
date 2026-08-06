<?php

namespace App\Policies;

use App\Models\IncubatedProject;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Support\IncubationAccess;

class IncubatedProjectPolicy
{
    use GrantsPlatformAdminFullAccess;

    public function viewAny(?User $user): bool
    {
        return IncubationAccess::hasViewAccess($user)
            || IncubationAccess::hasManageAccess($user);
    }

    public function view(?User $user, IncubatedProject $project): bool
    {
        if (!$user) {
            return false;
        }

        if ((int) $project->owner_user_id === (int) $user->id) {
            return true;
        }

        if ((int) $project->mentor_user_id === (int) $user->id) {
            return true;
        }

        if (IncubationAccess::hasManageAccess($user)) {
            return true;
        }

        if (!IncubationAccess::hasViewAccess($user)) {
            return false;
        }

        $project->loadMissing('incubator');

        if (IncubationAccess::isIncubatorManager($user, $project->incubator)) {
            return true;
        }

        if ($user->hasRole(['branch_manager', 'branch_officer']) && IncubationAccess::sharesBranch($user, $project->incubator)) {
            return true;
        }

        return $user->hasRole(['auditor', 'deputy_general_director', 'incubator_mentor']);
    }

    public function update(?User $user, IncubatedProject $project): bool
    {
        return IncubationAccess::hasManageAccess($user)
            || IncubationAccess::isIncubatorManager($user, $project->incubator);
    }

    public function submitProgressReport(?User $user, IncubatedProject $project): bool
    {
        if (!$user) {
            return false;
        }

        if ((int) $project->owner_user_id === (int) $user->id) {
            return true;
        }

        if ((int) $project->mentor_user_id === (int) $user->id) {
            return true;
        }

        if (IncubationAccess::hasManageAccess($user)) {
            return true;
        }

        $project->loadMissing('incubator');

        return IncubationAccess::isIncubatorManager($user, $project->incubator);
    }
}
