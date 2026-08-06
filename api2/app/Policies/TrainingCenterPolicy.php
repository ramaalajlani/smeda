<?php

namespace App\Policies;

use App\Models\TrainingCenter;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Policies\Concerns\TrainingPolicyHelpers;
use App\Support\BranchDataScope;
use App\Support\TrainingDataScope;

class TrainingCenterPolicy
{
    use GrantsPlatformAdminFullAccess;
    use TrainingPolicyHelpers;

    public function viewAny(?User $user): bool
    {
        return $this->hasPermission($user, 'view_centers');
    }

    public function view(?User $user, TrainingCenter $center): bool
    {
        if (!$this->hasPermission($user, 'view_centers')) {
            return false;
        }

        return TrainingDataScope::scopeTrainingCenters(TrainingCenter::query()->whereKey($center->id), $user)->exists();
    }

    public function create(?User $user): bool
    {
        return $this->hasPermission($user, 'manage_centers');
    }

    public function update(?User $user, TrainingCenter $center): bool
    {
        if (!$this->hasPermission($user, 'manage_centers')) {
            return false;
        }

        if ($this->hasUnrestricted($user)) {
            return true;
        }

        if (BranchDataScope::isBranchManager($user) && $user->branch_id) {
            return (int) $center->branch_id === (int) $user->branch_id;
        }

        return $user->isCenterUser() && $user->belongsToCenter($center->id);
    }

    public function printCertificate(?User $user, TrainingCenter $center): bool
    {
        return $this->hasPermission($user, 'print_certificates')
            && $this->view($user, $center);
    }
}
