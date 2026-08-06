<?php

namespace App\Support;

class PermissionModuleMapper
{
    /** @var array<string, list<string>> */
    private const PREFIX_MAP = [
        'trainers' => ['view_trainers', 'manage_trainers', 'view_trainer_profiles', 'edit_own_trainer_profile'],
        'training' => [
            'view_kits', 'manage_kits', 'nominate_training_kits', 'review_training_kit_nominations',
            'view_programs', 'manage_programs',
        ],
        'centers' => ['view_centers', 'manage_centers'],
        'courses' => ['view_courses', 'manage_courses', 'view_course_details'],
        'trainees' => ['view_trainees', 'manage_trainees'],
        'certificates' => [
            'view_certificates', 'issue_certificates', 'view_certificate_approvals',
            'approve_center_certificates', 'approve_training_certificates', 'approve_deputy_certificates',
            'print_certificates', 'verify_certificates',
        ],
        'registration_requests' => [
            'view_registration_requests', 'create_center_registration_requests',
            'review_center_registration_requests', 'create_trainer_registration_requests',
            'review_trainer_registration_requests', 'create_trainee_registration_requests',
            'review_trainee_registration_requests', 'create_course_registration_requests',
            'confirm_course_registration_requests', 'complete_course_registration_requests',
        ],
        'dashboard' => ['view_reports', 'view_audit'],
        'system' => [
            'manage_roles', 'view_roles', 'create_roles', 'update_roles', 'delete_roles',
            'manage_permissions', 'view_permissions', 'create_permissions', 'update_permissions', 'delete_permissions',
            'assign_roles', 'revoke_roles', 'assign_permissions', 'revoke_permissions',
            'view_users', 'manage_user_access',
        ],
    ];

    /**
     * @param iterable<string> $permissionNames
     * @return array<string, list<string>>
     */
    public static function group(iterable $permissionNames): array
    {
        $modules = array_fill_keys(array_merge(array_keys(self::PREFIX_MAP), ['training']), []);
        $assigned = [];

        foreach ($permissionNames as $name) {
            $module = self::resolveModule($name);
            $modules[$module][] = $name;
            $assigned[$name] = true;
        }

        foreach ($modules as $key => $items) {
            $modules[$key] = array_values(array_unique($items));
            sort($modules[$key]);
        }

        return array_filter($modules, fn (array $items) => $items !== []);
    }

    public static function resolveModule(string $permissionName): string
    {
        foreach (self::PREFIX_MAP as $module => $names) {
            if (in_array($permissionName, $names, true)) {
                return $module;
            }
        }

        if (str_contains($permissionName, 'kit') || str_contains($permissionName, 'program') || str_contains($permissionName, 'nomination')) {
            return 'training';
        }

        if (str_starts_with($permissionName, 'view_') || str_starts_with($permissionName, 'manage_')) {
            return 'training';
        }

        return 'system';
    }
}
