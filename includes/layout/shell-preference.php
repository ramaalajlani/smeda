<?php
/**
 * تفضيل قوقعة لوحة الدور (ds-sidebar) مقابل قوقعة المنصة العامة (app-shell).
 * يُقرأ من كوكي يضبطه auth.js عند تسجيل الدخول / تحديث /me.
 */
if (!function_exists('authority_dashboard_shell_roles')) {
    function authority_dashboard_shell_roles(): array
    {
        return [
            'project_services_manager',
            'training_manager',
            'branch_manager',
            'incubator_manager',
            'media_manager',
            'entrepreneur_manager',
            'general_director',
        ];
    }
}

if (!function_exists('authority_shell_role')) {
    function authority_shell_role(): ?string
    {
        $raw = isset($_COOKIE['authority_shell_role']) ? (string) $_COOKIE['authority_shell_role'] : '';
        $raw = preg_replace('/[^a-z0-9_]/', '', strtolower($raw));
        if ($raw === '') {
            return null;
        }
        return in_array($raw, authority_dashboard_shell_roles(), true) ? $raw : null;
    }
}

if (!function_exists('authority_use_dashboard_shell')) {
    function authority_use_dashboard_shell(): bool
    {
        if (!empty($GLOBALS['forceAppShell'])) {
            return false;
        }
        if (!empty($GLOBALS['forceDashboardShell'])) {
            return true;
        }
        return authority_shell_role() !== null;
    }
}
