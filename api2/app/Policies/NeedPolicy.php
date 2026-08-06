<?php

namespace App\Policies;

use App\Models\Need;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Support\AccessControlGuard;
use App\Support\NeedDataScope;
use App\Support\NeedStatus;

class NeedPolicy
{
    use GrantsPlatformAdminFullAccess;

    public function viewAny(?User $user): bool
    {
        return $user && (
            $user->hasPermissionTo('needs.view')
            || $user->hasPermissionTo('needs.view_all')
            || $user->hasPermissionTo('needs.view_branch')
        );
    }

    public function view(?User $user, Need $need): bool
    {
        return $this->viewAny($user) && NeedDataScope::canAccessNeed($user, $need);
    }

    public function create(?User $user): bool
    {
        return $user && !$user->hasRole('auditor') && (
            $user->hasPermissionTo('needs.create')
            || $user->hasPermissionTo('needs.create_citizen')
            || $user->hasPermissionTo('needs.create_state')
        );
    }

    public function createState(?User $user): bool
    {
        return $user && !$user->hasRole('auditor') && (
            AccessControlGuard::isNationalAdministrator($user)
            || $user->hasRole('branch_manager')
            || $user->hasPermissionTo('needs.create_state')
        );
    }

    public function update(?User $user, Need $need): bool
    {
        if (!$this->view($user, $need) || $user->hasRole('auditor')) {
            return false;
        }

        if (!NeedStatus::isEditable($need->status) && !$user->hasPermissionTo('needs.update')) {
            return false;
        }

        return $user->hasPermissionTo('needs.update')
            || $user->hasPermissionTo('needs.create')
            || (int) $need->created_by === (int) $user->id;
    }

    public function review(?User $user, Need $need): bool
    {
        return $this->view($user, $need)
            && !$user->hasRole('auditor')
            && $user->hasPermissionTo('needs.review');
    }

    public function approve(?User $user, Need $need): bool
    {
        return $this->view($user, $need)
            && !$user->hasRole('auditor')
            && $user->hasPermissionTo('needs.approve');
    }

    public function reject(?User $user, Need $need): bool
    {
        return $this->approve($user, $need) && $user->hasPermissionTo('needs.reject');
    }

    public function returnForEdit(?User $user, Need $need): bool
    {
        return $this->review($user, $need) && $user->hasPermissionTo('needs.return');
    }

    public function classify(?User $user, Need $need): bool
    {
        return $this->view($user, $need)
            && !$user->hasRole('auditor')
            && $user->hasPermissionTo('needs.classify');
    }

    public function resolve(?User $user, Need $need): bool
    {
        return $this->view($user, $need)
            && !$user->hasRole('auditor')
            && $user->hasPermissionTo('needs.resolve');
    }

    public function export(?User $user): bool
    {
        return $user && !$user->hasRole('auditor') && $user->hasPermissionTo('needs.export');
    }

    public function map(?User $user): bool
    {
        return $user && $user->hasPermissionTo('needs.map');
    }

    public function dashboard(?User $user): bool
    {
        return $user && $user->hasPermissionTo('needs.dashboard');
    }

    public function manageLookups(?User $user): bool
    {
        return $user && !$user->hasRole('auditor') && $user->hasPermissionTo('needs.manage_lookups');
    }

    public function manageAdminUnits(?User $user): bool
    {
        return $user && !$user->hasRole('auditor') && $user->hasPermissionTo('needs.manage_admin_units');
    }
}
