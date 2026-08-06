<?php

namespace App\Policies;

use App\Models\FinancialRecord;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Support\AccessControlGuard;
use App\Support\BranchDataScope;

class FinancialRecordPolicy
{
    use GrantsPlatformAdminFullAccess;

    public function viewAny(?User $user): bool
    {
        return $user && (
            AccessControlGuard::isNationalAdministrator($user)
            || AccessControlGuard::isNationalExecutive($user)
            || $user->hasRole('auditor')
            || BranchDataScope::isBranchManager($user)
            || $user->hasPermissionTo('view_finance')
            || $user->hasPermissionTo('manage_finance')
        );
    }

    public function view(?User $user, FinancialRecord $record): bool
    {
        if (!$this->viewAny($user)) {
            return false;
        }

        if (AccessControlGuard::isNationalAdministrator($user)
            || AccessControlGuard::isNationalExecutive($user)
            || $user->hasRole('auditor')) {
            return true;
        }

        if (BranchDataScope::isBranchManager($user) && $user->branch_id) {
            return (int) $record->branch_id === (int) $user->branch_id;
        }

        return $user->hasPermissionTo('view_finance') || $user->hasPermissionTo('manage_finance');
    }

    public function create(?User $user): bool
    {
        return $user && (
            AccessControlGuard::isNationalAdministrator($user)
            || $user->hasPermissionTo('manage_finance')
        );
    }

    public function update(?User $user, FinancialRecord $record): bool
    {
        return $this->create($user) && $this->view($user, $record);
    }

    public function approve(?User $user, FinancialRecord $record): bool
    {
        if (!$user || !$this->view($user, $record)) {
            return false;
        }

        if (AccessControlGuard::isNationalAdministrator($user)) {
            return true;
        }

        return AccessControlGuard::isNationalExecutive($user)
            && $user->hasPermissionTo('approve_finance');
    }
}
