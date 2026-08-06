<?php

namespace App\Support;

class AuditActionModule
{
    public static function resolve(string $action): string
    {
        if (str_starts_with($action, 'user_') || in_array($action, ['login_success', 'login_failed', 'logout'], true)) {
            return 'users';
        }

        if (str_starts_with($action, 'role_')) {
            return 'roles';
        }

        if (str_starts_with($action, 'permission_')) {
            return 'permissions';
        }

        if (str_contains($action, 'center')) {
            return 'centers';
        }

        if (str_contains($action, 'trainer')) {
            return 'trainers';
        }

        if (str_contains($action, 'trainee')) {
            return 'trainees';
        }

        if (str_contains($action, 'course')) {
            return 'courses';
        }

        if (str_contains($action, 'certificate')) {
            return 'certificates';
        }

        if (str_contains($action, 'registration')) {
            return 'registration_requests';
        }

        if (str_starts_with($action, 'branch_')) {
            return 'branches';
        }

        if (str_starts_with($action, 'agreement_')) {
            return 'agreements';
        }

        if (str_starts_with($action, 'financial_') || str_starts_with($action, 'finance_')) {
            return 'finance';
        }

        if (str_starts_with($action, 'need_')) {
            return 'needs';
        }

        if (str_starts_with($action, 'activity_logs_')) {
            return 'audit';
        }

        if (str_contains($action, 'workforce')) {
            return 'workforce';
        }

        return 'system';
    }
}
