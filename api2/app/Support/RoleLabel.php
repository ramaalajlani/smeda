<?php

namespace App\Support;

class RoleLabel
{
    /** @var array<string, string> */
    private const MAP = [
        'general_director' => 'المدير العام',
        'deputy_general_director' => 'نائب المدير العام',
        'branch_manager' => 'مدير فرع',
        'center_user' => 'مستخدم مركز',
        'trainer_user' => 'مدرب',
        'trainee_user' => 'متدرب',
        'auditor' => 'مدقق',
        'admin' => 'مدير نظام',
        'super_admin' => 'مدير نظام كامل',
        'system_admin' => 'مدير نظام (صلاحيات)',
        'training_manager' => 'مدير التدريب',
        'deputy_director' => 'نائب مدير',
    ];

    public static function label(?string $role): string
    {
        if ($role === null || $role === '') {
            return '—';
        }

        return self::MAP[$role] ?? $role;
    }

    /**
     * @param  array<int, string>  $roles
     * @return array<int, string>
     */
    public static function labels(array $roles): array
    {
        return array_map(fn (string $role) => self::label($role), $roles);
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::MAP;
    }
}
