<?php

namespace App\Policies;

use App\Models\ConsultantAssignment;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Support\AccessControlGuard;
use App\Support\FinanceDataScope;

class ConsultantAssignmentPolicy
{
    use GrantsPlatformAdminFullAccess;

    public function view(?User $user, ConsultantAssignment $assignment): bool
    {
        if (!$user) {
            return false;
        }

        $application = $assignment->application ?? $assignment->application()->first();
        if (!$application) {
            return false;
        }

        return FinanceDataScope::canAccessApplication($user, $application);
    }

    public function accept(?User $user, ConsultantAssignment $assignment): bool
    {
        return $this->view($user, $assignment)
            && !$user->hasRole('auditor')
            && $user->hasRole('consultant_office')
            && (int) $user->consultant_office_id === (int) $assignment->consultant_office_id;
    }

    public function submitPrice(?User $user, ConsultantAssignment $assignment): bool
    {
        if ($this->accept($user, $assignment)) {
            return $user->hasPermissionTo('finance.consultant_assignments.submit_price')
                || $user->hasPermissionTo('finance.consultants.submit_price');
        }

        return $this->view($user, $assignment)
            && !$user->hasRole('auditor')
            && $user->hasPermissionTo('finance.consultants.assign');
    }

    public function approvePrice(?User $user, ConsultantAssignment $assignment): bool
    {
        return $this->view($user, $assignment)
            && !$user->hasRole('auditor')
            && (
                AccessControlGuard::isNationalAdministrator($user)
                || $user->hasPermissionTo('finance.applications.assign_consultant')
            );
    }
}
