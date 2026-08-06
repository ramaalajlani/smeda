<?php

namespace App\Support;

use App\Models\Need;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class NeedDataScope
{
    /** @var list<string> */
    public const NATIONAL_VIEW_ROLES = [
        'general_director', 'admin', 'super_admin',
        'development_manager', 'local_development_manager', 'project_services_manager',
    ];

    public static function hasNationalNeedsAccess(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (AccessControlGuard::isNationalAdministrator($user)) {
            return true;
        }

        if ($user->hasAnyRole(self::NATIONAL_VIEW_ROLES) && $user->can('needs.view_all')) {
            return true;
        }

        if ($user->hasAnyRole(['deputy_general_director', 'deputy_director'])
            && $user->can('needs.view_all')) {
            return true;
        }

        if ($user->hasRole('auditor') && $user->can('needs.view')) {
            return true;
        }

        return $user->can('needs.view_all');
    }

    public static function isGovernor(?User $user): bool
    {
        return $user && $user->hasRole('governor');
    }

    public static function scopeNeeds(Builder $query, ?User $user): Builder
    {
        if (!$user) {
            return $query->whereRaw('0 = 1');
        }

        // مدير عام / وطني → يرى كل شيء
        if (self::hasNationalNeedsAccess($user)) {
            if (BranchDataScope::isBranchManager($user) && $user->branch_id && !$user->can('needs.view_all')) {
                return $query->where('branch_id', $user->branch_id);
            }

            return $query;
        }

        // محافظ → يرى احتياجات محافظته فقط
        if (self::isGovernor($user)) {
            if ($user->governorate_id) {
                return $query->where('governorate_id', $user->governorate_id);
            }

            return $query->whereRaw('0 = 1');
        }

        // مدير فرع → يرى كل احتياجات فرعه (مواطن + حكومة، عام + خاص)
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            return $query->where('branch_id', $user->branch_id);
        }

        // مدخل/مدقق بيانات مرتبط بفرع → نطاق الفرع مباشرة (الأسرع)
        if ($user->branch_id && $user->hasAnyRole(['data_entry', 'data_reviewer', 'branch_officer'])) {
            return $query->where('branch_id', $user->branch_id);
        }

        // باقي الأدوار المرتبطة بفرع
        if ($user->branch_id) {
            return $query->where('branch_id', $user->branch_id);
        }

        if ($user->hasAnyRole(['data_entry', 'data_reviewer'])) {
            return $query->where('created_by', $user->id);
        }

        if ($user->can('needs.view')) {
            return $query->where('created_by', $user->id);
        }

        return $query->whereRaw('0 = 1');
    }

    public static function canAccessNeed(?User $user, Need $need): bool
    {
        return self::scopeNeeds(Need::query()->whereKey($need->id), $user)->exists();
    }

    public static function assertGovernorateScope(?User $user, ?int $governorateId, ?int $branchId): void
    {
        if (!$user || self::hasNationalNeedsAccess($user)) {
            return;
        }

        if (self::isGovernor($user)) {
            if ($user->governorate_id && $governorateId && (int) $user->governorate_id !== (int) $governorateId) {
                abort(403, 'لا يمكنك العمل خارج نطاق محافظتك.');
            }

            return;
        }

        if ($user->branch_id && $branchId && (int) $user->branch_id !== (int) $branchId) {
            abort(403, 'لا يمكنك العمل خارج نطاق فرعك.');
        }

        if ($user->governorate_id && $governorateId && (int) $user->governorate_id !== (int) $governorateId) {
            abort(403, 'لا يمكنك العمل خارج نطاق محافظتك.');
        }
    }
}
