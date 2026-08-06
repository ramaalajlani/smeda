<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Support\AccessControlGuard;

trait GrantsPlatformAdminFullAccess
{
    /**
     * مدير المنصة (admin / super_admin) يتجاوز فحوصات الصلاحيات والنطاق في سياسات التدريب.
     * لا يُستخدم في سياسات إدارة المستخدمين (UserAccessPolicy) للحفاظ على حماية التعديل الذاتي.
     */
    public function before(User $user, string $ability): ?bool
    {
        if (AccessControlGuard::isPlatformAdministrator($user)) {
            return true;
        }

        return null;
    }
}
