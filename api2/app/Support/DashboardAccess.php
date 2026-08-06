<?php

namespace App\Support;

use App\Models\User;

class DashboardAccess
{
    /** أدوار لها لوحة رئيسية أو بيانات في /api/dashboard */
    private const MAIN_DASHBOARD_ROLES = [
        'general_director', 'admin', 'super_admin',
        'deputy_general_director', 'deputy_director',
        'branch_manager', 'branch_officer', 'governor',
        'training_manager', 'system_admin',
        'center_user', 'trainer_user', 'trainee_user',
        'auditor', 'finance_manager', 'finance_officer',
        'funding_partner', 'consultant_office',
        'consultant_union_admin', 'central_bank_admin',
        'data_entry', 'data_reviewer',
        'development_manager', 'local_development_manager',
        'project_services_manager', 'project_owner',
        'workforce_manager',
        'incubator_manager', 'incubator_mentor',
        'media_manager', 'entrepreneur_manager',
    ];

    public static function canAccessMainDashboard(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (AccessControlGuard::isNationalAdministrator($user)) {
            return true;
        }

        return $user->hasAnyRole(self::MAIN_DASHBOARD_ROLES);
    }

    public static function assertMainDashboardAccess(?User $user): void
    {
        if (!static::canAccessMainDashboard($user)) {
            abort(403, 'لا توجد لوحة بيانات متاحة لهذا الدور.');
        }
    }

    public static function isFinanceManager(?User $user): bool
    {
        return $user && $user->hasRole('finance_manager');
    }

    public static function isFinanceOfficer(?User $user): bool
    {
        return $user && $user->hasRole('finance_officer');
    }

    public static function isScopedInstitutionalPartner(?User $user): bool
    {
        return $user && $user->hasAnyRole(['funding_partner', 'consultant_office']);
    }
}
