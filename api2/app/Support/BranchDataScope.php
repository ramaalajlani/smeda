<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * عزل البيانات حسب المحافظة/الفرع — 14 محافظة سورية.
 */
class BranchDataScope
{
    /** @var list<string> */
    public const SYRIAN_GOVERNORATES = [
        ['code' => 'damascus', 'name_ar' => 'دمشق', 'name_en' => 'Damascus'],
        ['code' => 'rural_damascus', 'name_ar' => 'ريف دمشق', 'name_en' => 'Rural Damascus'],
        ['code' => 'aleppo', 'name_ar' => 'حلب', 'name_en' => 'Aleppo'],
        ['code' => 'homs', 'name_ar' => 'حمص', 'name_en' => 'Homs'],
        ['code' => 'hama', 'name_ar' => 'حماة', 'name_en' => 'Hama'],
        ['code' => 'latakia', 'name_ar' => 'اللاذقية', 'name_en' => 'Latakia'],
        ['code' => 'tartus', 'name_ar' => 'طرطوس', 'name_en' => 'Tartus'],
        ['code' => 'idlib', 'name_ar' => 'إدلب', 'name_en' => 'Idlib'],
        ['code' => 'daraa', 'name_ar' => 'درعا', 'name_en' => 'Daraa'],
        ['code' => 'suweida', 'name_ar' => 'السويداء', 'name_en' => 'Suweida'],
        ['code' => 'quneitra', 'name_ar' => 'القنيطرة', 'name_en' => 'Quneitra'],
        ['code' => 'deir_ez_zor', 'name_ar' => 'دير الزور', 'name_en' => 'Deir ez-Zor'],
        ['code' => 'raqqa', 'name_ar' => 'الرقة', 'name_en' => 'Raqqa'],
        ['code' => 'hasakah', 'name_ar' => 'الحسكة', 'name_en' => 'Hasakah'],
    ];

    public static function hasNationalAccess(?User $user): bool
    {
        return AccessControlGuard::isNationalAdministrator($user);
    }

    public static function hasNationalReadAccess(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return self::hasNationalAccess($user)
            || AccessControlGuard::isNationalExecutive($user)
            || $user->isAuditor()
            || $user->isTrainingManager()
            || $user->hasRole('project_services_manager');
    }

    public static function isBranchManager(?User $user): bool
    {
        return $user?->hasRole('branch_manager') ?? false;
    }

    public static function applyBranchScope(Builder $query, ?User $user, string $branchColumn = 'branch_id'): Builder
    {
        if (!$user || self::hasNationalReadAccess($user)) {
            return $query;
        }

        if (self::isBranchManager($user)) {
            if (!$user->branch_id) {
                return $query->whereRaw('0 = 1');
            }

            return $query->where($branchColumn, $user->branch_id);
        }

        return $query;
    }

    public static function userBelongsToBranch(?User $user, int|string|null $branchId): bool
    {
        if (!$user || blank($branchId)) {
            return false;
        }

        if (self::hasNationalReadAccess($user)) {
            return true;
        }

        return self::isBranchManager($user) && (int) $user->branch_id === (int) $branchId;
    }
}
