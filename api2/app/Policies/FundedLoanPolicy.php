<?php

namespace App\Policies;

use App\Models\FundedLoan;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Support\AccessControlGuard;
use App\Support\FinanceDataScope;

class FundedLoanPolicy
{
    use GrantsPlatformAdminFullAccess;

    public function viewAny(?User $user): bool
    {
        return $user && (
            FinanceDataScope::hasNationalFinanceAccess($user)
            || $user->hasRole(['funding_partner', 'project_owner'])
            || $user->hasPermissionTo('finance.loans.view')
        );
    }

    public function view(?User $user, FundedLoan $loan): bool
    {
        if (!$this->viewAny($user)) {
            return false;
        }

        return FinanceDataScope::scopeLoans(FundedLoan::query()->whereKey($loan->id), $user)->exists();
    }

    public function create(?User $user): bool
    {
        return $user && !$user->hasRole('auditor') && (
            AccessControlGuard::isNationalAdministrator($user)
            || $user->hasPermissionTo('finance.loans.manage')
        );
    }

    public function update(?User $user, FundedLoan $loan): bool
    {
        return $this->create($user) && $this->view($user, $loan);
    }

    public function recordPayment(?User $user, FundedLoan $loan): bool
    {
        return $this->view($user, $loan)
            && !$user->hasRole('auditor')
            && (
                AccessControlGuard::isNationalAdministrator($user)
                || $user->hasPermissionTo('finance.loans.payments')
            );
    }

    public function markDefaulted(?User $user, FundedLoan $loan): bool
    {
        return $this->view($user, $loan)
            && !$user->hasRole('auditor')
            && (
                AccessControlGuard::isNationalAdministrator($user)
                || $user->hasPermissionTo('finance.loans.defaulted')
            );
    }
}
