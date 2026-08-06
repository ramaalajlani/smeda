<?php



namespace App\Support;



use App\Models\User;

use Illuminate\Validation\ValidationException;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;



class AccessControlGuard

{

    public const GUARD = 'sanctum';



    /**

     * المدير العام للمؤسسة — أعلى سلطة وطنية (FULL_PLATFORM_ACCESS).

     *

     * @var list<string>

     */

    public const NATIONAL_ADMIN_ROLES = ['general_director', 'admin', 'super_admin'];



    /**

     * نائب المدير العام — صلاحيات وطنية واسعة (قراءة/اعتماد) دون إدارة كاملة.

     *

     * @var list<string>

     */

    public const NATIONAL_EXECUTIVE_ROLES = ['deputy_general_director', 'deputy_director'];

    /**
     * أدوار قيادية أعلى من مدير المنصة — لا تُفوَّض إلا من المدير العام أو مدير النظام الكامل.
     */
    public const EXECUTIVE_ASSIGN_ROLES = ['general_director', 'deputy_general_director'];



    /** @deprecated use NATIONAL_ADMIN_ROLES */

    public const PLATFORM_ADMIN_ROLES = self::NATIONAL_ADMIN_ROLES;



    /** @var list<string> */

    public const PROTECTED_ROLES = ['general_director', 'admin', 'system_admin', 'super_admin'];



    public static function isNationalAdministrator(?User $user): bool

    {

        if (!$user) {

            return false;

        }



        return $user->hasRole(self::NATIONAL_ADMIN_ROLES);

    }



    /** @deprecated use isNationalAdministrator */

    public static function isPlatformAdministrator(?User $user): bool

    {

        return self::isNationalAdministrator($user);

    }



    public static function isNationalExecutive(?User $user): bool

    {

        if (!$user) {

            return false;

        }



        return $user->hasRole(self::NATIONAL_EXECUTIVE_ROLES);

    }



    public static function isBranchManager(?User $user): bool

    {

        return $user?->hasRole('branch_manager') ?? false;

    }



    /** @var list<string> */

    public const CORE_PERMISSIONS = [

        'view_trainers', 'manage_trainers', 'view_centers', 'manage_centers',

        'view_courses', 'manage_courses', 'view_certificates', 'issue_certificates',

        'manage_roles', 'manage_permissions', 'manage_user_access', 'view_users',

        'view_governorates', 'view_branches', 'manage_branches',

    ];



    public static function isAccessAdministrator(?User $user): bool

    {

        if (!$user) {

            return false;

        }



        return $user->hasRole(self::PROTECTED_ROLES);

    }



    public static function isProtectedRole(string $roleName): bool

    {

        return in_array($roleName, self::PROTECTED_ROLES, true);

    }



    public static function canAssignProtectedRole(?User $actor, string $roleName): bool

    {

        if (!self::isProtectedRole($roleName)) {

            return true;

        }



        return self::isAccessAdministrator($actor);

    }



    public static function adminUserCount(): int

    {

        return User::query()

            ->whereHas('roles', fn ($q) => $q->whereIn('name', self::PROTECTED_ROLES))

            ->count();

    }



    public static function userHasAdminRole(User $user): bool

    {

        return $user->hasRole(self::PROTECTED_ROLES);

    }



    public static function assertCanModifyUserAccess(?User $actor, User $target): void

    {

        if (!$actor) {

            throw ValidationException::withMessages(['user' => ['غير مصرح.']]);

        }



        if (!self::isAccessAdministrator($actor)) {

            throw ValidationException::withMessages(['user' => ['فقط مدير النظام يمكنه إدارة الصلاحيات.']]);

        }



        if ((int) $actor->id === (int) $target->id) {

            throw ValidationException::withMessages(['user' => ['لا يمكنك تعديل صلاحياتك الذاتية.']]);

        }

    }



    public static function assertCanRevokeRole(User $actor, User $target, Role $role): void
    {
        if (self::isAccessAdministrator($actor)) {
            self::assertCanModifyUserAccess($actor, $target);
        } else {
            self::assertParentCanManage($actor, $target);

            if (self::isProtectedRole($role->name) || in_array($role->name, self::EXECUTIVE_ASSIGN_ROLES, true)) {
                throw ValidationException::withMessages([
                    'role' => ['لا يمكنك إزالة هذا الدور المحمي.'],
                ]);
            }

            return;
        }

        if (!self::isProtectedRole($role->name)) {
            return;
        }

        if (self::userHasAdminRole($target) && self::adminUserCount() <= 1) {
            throw ValidationException::withMessages([
                'role' => ['لا يمكن إزالة آخر مدير نظام.'],
            ]);
        }
    }



    public static function assertRoleDeletable(Role $role): void

    {

        if (self::isProtectedRole($role->name)) {

            throw ValidationException::withMessages([

                'role' => ['لا يمكن حذف دور محمي (' . $role->name . ').'],

            ]);

        }



        if ($role->users()->count() > 0) {

            throw ValidationException::withMessages([

                'role' => ['لا يمكن حذف دور مرتبط بمستخدمين.'],

            ]);

        }

    }



    public static function assertPermissionDeletable(string $permissionName): void

    {

        if (in_array($permissionName, self::CORE_PERMISSIONS, true)) {

            throw ValidationException::withMessages([

                'permission' => ['لا يمكن حذف صلاحية أساسية للنظام.'],

            ]);

        }

    }

    // ══════════════════════════════════════════════════════════════
    // Parent-Child delegation hierarchy
    // ══════════════════════════════════════════════════════════════

    /**
     * هرمية الأدوار: كل مستوى يمكنه إنشاء/إدارة الأدوار أدناه فقط.
     * المدير العام وادمن لا قيود عليهم (handled separately).
     */
    public const ROLE_HIERARCHY = [
        'deputy_general_director' => [
            'branch_manager', 'training_manager', 'training_supervisor', 'auditor',
            'center_user', 'trainer_user', 'trainee_user', 'branch_officer',
            'data_entry', 'data_reviewer', 'governor', 'project_services_manager',
        ],
        'deputy_director' => [
            'branch_manager', 'training_manager', 'training_supervisor', 'auditor',
            'center_user', 'trainer_user', 'trainee_user', 'branch_officer',
            'data_entry', 'data_reviewer', 'governor',
        ],
        'project_services_manager' => [
            'branch_manager', 'branch_officer', 'governor',
            'data_entry', 'data_reviewer',
            'training_supervisor', 'center_user', 'trainer_user', 'trainee_user',
        ],
        'branch_manager' => [
            'branch_officer', 'training_supervisor', 'center_user', 'trainer_user', 'trainee_user',
            'data_entry', 'data_reviewer',
        ],
        'branch_officer' => [
            'center_user', 'trainer_user', 'trainee_user',
        ],
        'training_manager' => [
            'center_user', 'trainer_user', 'trainee_user',
        ],
        'center_user' => [
            'trainer_user', 'trainee_user',
        ],
    ];

    /**
     * هل يمكن للمستخدم الأب إنشاء/إدارة ابن بالدور المحدد؟
     */
    public static function parentCanDelegateRole(User $parent, string $targetRole): bool
    {
        // المدير العام ونائب المدير العام أعلى من مدير المنصة — لا يُمنحان إلا من المدير العام / مدير النظام الكامل
        if (in_array($targetRole, self::EXECUTIVE_ASSIGN_ROLES, true)) {
            return $parent->hasRole(['general_director', 'super_admin']);
        }

        // المدير العام/أدمن → بدون قيود (عدا الأدوار أعلاه)
        if (self::isNationalAdministrator($parent)) {
            return true;
        }

        // نظر في كل دور يملكه الأب
        foreach ($parent->getRoleNames() as $parentRole) {
            if (isset(self::ROLE_HIERARCHY[$parentRole])
                && in_array($targetRole, self::ROLE_HIERARCHY[$parentRole], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * هل يملك الأب الصلاحية المطلوبة ليمنحها لابنه؟
     * Admin يملك كل شيء. غير Admin → يجب أن يملكها فعلاً.
     */
    public static function parentCanDelegatePermission(User $parent, string $permissionName): bool
    {
        if (self::isNationalAdministrator($parent)) {
            return true;
        }

        return $parent->hasPermissionTo($permissionName, self::GUARD);
    }

    /**
     * هل يمكن للمستخدم إدارة مستخدم آخر (تعديل أدواره/صلاحياته)؟
     * - Admin: دائماً نعم
     * - الأب المباشر: نعم بشرط أن يكون الابن هو parent_user_id = actor->id
     */
    public static function canActorManageTarget(User $actor, User $target): bool
    {
        if (self::isNationalAdministrator($actor)) {
            return true;
        }

        // الأب المباشر
        if ((int) $target->parent_user_id === (int) $actor->id) {
            return true;
        }

        // مدير خدمات المشروعات: إدارة حسابات ضمن هرمية التفويض (فروع / محافظة / مدخلين / مدققين)
        if ($actor->hasRole('project_services_manager')) {
            $targetRoles = $target->getRoleNames()->all();
            if ($targetRoles === []) {
                return (int) $target->parent_user_id === (int) $actor->id;
            }
            foreach ($targetRoles as $roleName) {
                if (self::isProtectedRole($roleName) || in_array($roleName, self::EXECUTIVE_ASSIGN_ROLES, true)) {
                    return false;
                }
            }
            foreach ($targetRoles as $roleName) {
                if (! self::parentCanDelegateRole($actor, $roleName)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * نسخة محدّثة من assertCanModifyUserAccess تدعم نظام الأب-الابن.
     */
    public static function assertParentCanManage(User $actor, User $target): void
    {
        if ((int) $actor->id === (int) $target->id) {
            throw ValidationException::withMessages(['user' => ['لا يمكنك تعديل صلاحياتك الذاتية.']]);
        }

        if (!self::canActorManageTarget($actor, $target)) {
            throw ValidationException::withMessages(['user' => [
                'لا يمكنك إدارة هذا المستخدم. يمكنك فقط إدارة مستخدميك المباشرين.',
            ]]);
        }
    }

    /**
     * قائمة الصلاحيات التي يملكها المستخدم ويمكنه تفويضها.
     */
    public static function getDelegatablePermissions(User $parent): array
    {
        if (self::isNationalAdministrator($parent)) {
            return Permission::query()
                ->where('guard_name', self::GUARD)
                ->pluck('name')
                ->all();
        }

        return $parent->getAllPermissions()->pluck('name')->all();
    }

    /**
     * قائمة الأدوار التي يمكن للأب تفويضها.
     */
    public static function getDelegatableRoles(User $parent): array
    {
        if (self::isNationalAdministrator($parent)) {
            $roles = Role::query()->where('guard_name', self::GUARD)->pluck('name')->all();
        } else {
            $delegatable = [];
            foreach ($parent->getRoleNames() as $parentRole) {
                if (isset(self::ROLE_HIERARCHY[$parentRole])) {
                    $delegatable = array_merge($delegatable, self::ROLE_HIERARCHY[$parentRole]);
                }
            }
            $roles = array_unique($delegatable);
        }

        // إخفاء المدير العام ونائبه عن مدير المنصة ومن هم دونه
        if (!$parent->hasRole(['general_director', 'super_admin'])) {
            $roles = array_values(array_filter(
                $roles,
                static fn (string $role) => ! in_array($role, self::EXECUTIVE_ASSIGN_ROLES, true)
            ));
        }

        return $roles;
    }
}

