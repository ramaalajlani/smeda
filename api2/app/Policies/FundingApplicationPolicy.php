<?php

namespace App\Policies;

use App\Models\FundingApplication;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Support\AccessControlGuard;
use App\Support\BranchDataScope;
use App\Support\FinanceDataScope;

class FundingApplicationPolicy
{
    use GrantsPlatformAdminFullAccess;

    public function viewAny(?User $user): bool
    {
        return $user && (
            FinanceDataScope::hasNationalFinanceAccess($user)
            || BranchDataScope::isBranchManager($user)
            || $user->hasRole(['consultant_office', 'funding_partner', 'project_owner'])
            || $user->hasPermissionTo('finance.applications.view')
        );
    }

    public function view(?User $user, FundingApplication $application): bool
    {
        return $this->viewAny($user) && FinanceDataScope::canAccessApplication($user, $application);
    }

    public function create(?User $user): bool
    {
        return $user && (
            AccessControlGuard::isNationalAdministrator($user)
            || $user->hasRole('project_owner')
            || $user->hasPermissionTo('finance.applications.create')
        );
    }

    public function update(?User $user, FundingApplication $application): bool
    {
        if (!$this->view($user, $application)) {
            return false;
        }

        if ($user->hasRole('auditor')) {
            return false;
        }

        if ($user->hasRole('project_owner')) {
            return in_array($application->status, ['draft', 'needs_completion'], true);
        }

        return AccessControlGuard::isNationalAdministrator($user)
            || BranchDataScope::isBranchManager($user)
            || $user->hasPermissionTo('finance.applications.update');
    }

    public function submit(?User $user, FundingApplication $application): bool
    {
        return $this->update($user, $application)
            && in_array($application->status, ['draft', 'needs_completion'], true);
    }

    public function reviewBranch(?User $user, FundingApplication $application): bool
    {
        return $this->view($user, $application)
            && !$user->hasRole('auditor')
            && (
                AccessControlGuard::isNationalAdministrator($user)
                || (BranchDataScope::isBranchManager($user) && (int) $application->branch_id === (int) $user->branch_id)
                || $user->hasPermissionTo('finance.applications.review_branch')
            );
    }

    public function assignConsultant(?User $user, FundingApplication $application): bool
    {
        return $this->view($user, $application)
            && !$user->hasRole('auditor')
            && (
                AccessControlGuard::isNationalAdministrator($user)
                || $user->hasPermissionTo('finance.applications.assign_consultant')
            );
    }

    public function assignPartner(?User $user, FundingApplication $application): bool
    {
        return $this->view($user, $application)
            && !$user->hasRole('auditor')
            && (
                AccessControlGuard::isNationalAdministrator($user)
                || $user->hasPermissionTo('finance.applications.assign_partner')
            );
    }

    public function approve(?User $user, FundingApplication $application): bool
    {
        return $this->view($user, $application)
            && !$user->hasRole('auditor')
            && (
                AccessControlGuard::isNationalAdministrator($user)
                || (AccessControlGuard::isNationalExecutive($user) && $user->hasPermissionTo('finance.applications.approve'))
            );
    }

    public function reject(?User $user, FundingApplication $application): bool
    {
        return $this->approve($user, $application)
            || ($this->reviewBranch($user, $application) && $user->hasPermissionTo('finance.applications.reject'));
    }
}
