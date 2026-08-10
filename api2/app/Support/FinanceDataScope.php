<?php

namespace App\Support;

use App\Models\FundingApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class FinanceDataScope
{
    public static function hasNationalFinanceAccess(?User $user): bool
    {
        return $user && (
            AccessControlGuard::isNationalAdministrator($user)
            || $user->hasRole('finance_manager')
            || ($user->hasRole('auditor') && $user->hasPermissionTo('finance.applications.view'))
        );
    }

    public static function scopeApplications(Builder $query, ?User $user): Builder
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (AccessControlGuard::isNationalAdministrator($user) || $user->hasRole('finance_manager')) {
            return $query;
        }

        if ($user->hasRole(['deputy_general_director', 'deputy_director'])
            && $user->hasPermissionTo('finance.applications.view')) {
            return $query;
        }

        if (BranchDataScope::isBranchManager($user)) {
            if ($user->branch_id) {
                return $query->where('branch_id', $user->branch_id);
            }

            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('branch_officer')) {
            return $query->where(function (Builder $q) use ($user) {
                if ($user->branch_id) {
                    $q->where('branch_id', $user->branch_id);
                } else {
                    $q->whereRaw('1 = 0');
                }
                $q->orWhere('created_by', $user->id)
                    ->orWhere('applicant_user_id', $user->id);
            });
        }

        if ($user->hasRole('consultant_union_admin')) {
            return $query->whereHas('consultantAssignments');
        }

        if ($user->hasRole('central_bank_admin')) {
            return $query->whereHas('partnerAssignments');
        }

        if ($user->hasRole('consultant_office') && $user->consultant_office_id) {
            return $query->whereHas('consultantAssignments', function (Builder $q) use ($user) {
                $q->where('consultant_office_id', $user->consultant_office_id);
            });
        }

        if ($user->hasRole('funding_partner') && $user->funding_partner_id) {
            return $query->whereHas('partnerAssignments', function (Builder $q) use ($user) {
                $q->where('funding_partner_id', $user->funding_partner_id);
            });
        }

        if ($user->hasRole('project_owner')) {
            return $query->where(function (Builder $q) use ($user) {
                $q->where('applicant_user_id', $user->id)
                    ->orWhere('created_by', $user->id);
            });
        }

        if ($user->hasRole('auditor') && $user->hasPermissionTo('finance.applications.view')) {
            return $query;
        }

        if ($user->hasRole('finance_officer')) {
            if ($user->branch_id) {
                return $query->where('branch_id', $user->branch_id);
            }

            if ($user->hasPermissionTo('finance.metrics.national')) {
                return $query;
            }

            return $query->whereRaw('1 = 0');
        }

        if ($user->hasPermissionTo('finance.applications.view')) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canAccessApplication(?User $user, FundingApplication $application): bool
    {
        return static::scopeApplications(
            FundingApplication::query()->whereKey($application->id),
            $user
        )->exists();
    }

    public static function scopeLoans(Builder $query, ?User $user, string $branchColumn = 'branch_id'): Builder
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (static::hasNationalFinanceAccess($user) || $user->hasPermissionTo('finance.loans.view')) {
            if (BranchDataScope::isBranchManager($user)) {
                if ($user->branch_id) {
                    return $query->whereHas('application', fn (Builder $q) => $q->where('branch_id', $user->branch_id));
                }

                return $query->whereRaw('1 = 0');
            }

            return $query;
        }

        if ($user->hasRole('central_bank_admin')) {
            return $query;
        }

        if ($user->hasRole('funding_partner') && $user->funding_partner_id) {
            return $query->where('funding_partner_id', $user->funding_partner_id);
        }

        if ($user->hasRole('project_owner')) {
            return $query->whereHas('application', function (Builder $q) use ($user) {
                $q->where('applicant_user_id', $user->id)->orWhere('created_by', $user->id);
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
