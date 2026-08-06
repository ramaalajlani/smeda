<?php

namespace App\Support;

use App\Models\ConsultingRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ConsultingDataScope
{
    /** @var list<string> */
    private const NATIONAL_ROLES = [
        'admin', 'super_admin', 'system_admin', 'general_director',
        'project_services_manager', 'consultant_union_admin',
    ];

    public static function hasNationalAccess(?User $user): bool
    {
        return $user && $user->hasRole(self::NATIONAL_ROLES);
    }

    public static function scopeForUser(Builder $query, ?User $user): Builder
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (self::hasNationalAccess($user)) {
            return $query;
        }

        if ($user->hasRole('branch_manager') && $user->branch_id) {
            return $query->where('branch_id', $user->branch_id);
        }

        if ($user->hasRole('branch_officer')) {
            return $query->where(function (Builder $q) use ($user) {
                if ($user->branch_id) {
                    $q->where('branch_id', $user->branch_id);
                } else {
                    $q->whereRaw('1 = 0');
                }
                $q->orWhere('user_id', $user->id);
            });
        }

        if ($user->hasRole('governor') && $user->governorate_id) {
            return $query->where('governorate_id', $user->governorate_id);
        }

        return $query->where('user_id', $user->id);
    }

    public static function canAccessRequest(?User $user, ConsultingRequest $request): bool
    {
        if (!$user) {
            return false;
        }

        if (self::hasNationalAccess($user)) {
            return true;
        }

        if ($user->hasRole(['branch_manager', 'branch_officer']) && $user->branch_id) {
            if ((int) $request->branch_id === (int) $user->branch_id) {
                return true;
            }
        }

        if ($user->hasRole('branch_officer') && (int) $request->user_id === (int) $user->id) {
            return true;
        }

        if ($user->hasRole('governor') && $user->governorate_id) {
            return (int) $request->governorate_id === (int) $user->governorate_id;
        }

        if ((int) $request->user_id === (int) $user->id) {
            return true;
        }

        $office = \App\Models\ConsultingOffice::query()->where('user_id', $user->id)->first();
        if ($office && $request->offers()->where('office_id', $office->id)->exists()) {
            return true;
        }

        return false;
    }
}
