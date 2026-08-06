<?php

namespace App\Support;

use App\Models\ConsultantOffice;
use App\Models\FundingPartner;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class InstitutionalPartnerScope
{
    public static function scopeConsultantOffices(Builder $query, ?User $user): Builder
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (AccessControlGuard::isNationalAdministrator($user)
            || $user->hasRole('consultant_union_admin')
            || ($user->hasRole('auditor') && $user->hasPermissionTo('finance.consultants.view_all'))) {
            return $query;
        }

        if ($user->hasRole('consultant_office') && $user->consultant_office_id) {
            return $query->whereKey($user->consultant_office_id);
        }

        if ($user->hasPermissionTo('finance.consultants.view')
            || $user->hasPermissionTo('finance.applications.assign_consultant')) {
            return $query->whereIn('status', ConsultantOffice::assignableStatuses());
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canAccessConsultantOffice(?User $user, ConsultantOffice $office): bool
    {
        return static::scopeConsultantOffices(
            ConsultantOffice::query()->whereKey($office->id),
            $user
        )->exists();
    }

    public static function scopeFundingPartners(Builder $query, ?User $user): Builder
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (AccessControlGuard::isNationalAdministrator($user)
            || $user->hasRole('central_bank_admin')
            || ($user->hasRole('auditor') && $user->hasPermissionTo('finance.partners.view_all'))) {
            return $query;
        }

        if ($user->hasRole('funding_partner') && $user->funding_partner_id) {
            return $query->whereKey($user->funding_partner_id);
        }

        if ($user->hasPermissionTo('finance.partners.view')
            || $user->hasPermissionTo('finance.applications.assign_partner')) {
            return $query->whereIn('status', FundingPartner::assignableStatuses());
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canAccessFundingPartner(?User $user, FundingPartner $partner): bool
    {
        return static::scopeFundingPartners(
            FundingPartner::query()->whereKey($partner->id),
            $user
        )->exists();
    }
}
