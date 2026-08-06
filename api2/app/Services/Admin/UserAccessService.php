<?php

namespace App\Services\Admin;

use App\Models\Branch;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\AccessControlGuard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserAccessService
{
    public function __construct(private AuditLogService $auditLog) {}

    public function paginateUsers(int $perPage = 20, array $filters = [], ?User $actor = null): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return User::query()
            ->select([
                'id', 'name', 'email', 'phone', 'entity_type', 'is_active',
                'governorate_id', 'branch_id',
                'training_center_id', 'training_supervisor_id', 'trainer_id', 'trainee_id', 'last_login_at', 'created_at',
                'parent_user_id',
            ])
            ->when($actor && ! AccessControlGuard::isNationalAdministrator($actor) && ! AccessControlGuard::isAccessAdministrator($actor), function ($q) use ($actor) {
                $roles = AccessControlGuard::getDelegatableRoles($actor);
                $q->where(function ($scoped) use ($actor, $roles) {
                    $scoped->where('parent_user_id', $actor->id);
                    if ($roles !== []) {
                        $scoped->orWhereHas('roles', fn ($role) => $role->whereIn('name', $roles));
                    }
                });
            })
            ->when($search, function ($q) use ($search) {
                if (mb_strlen(trim($search)) < 2) {
                    return;
                }

                $term = '%'.trim($search).'%';

                $q->where(function ($nested) use ($term) {
                    $nested->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function ($q) use ($filters) {
                $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            })
            ->when(!empty($filters['role']), function ($q) use ($filters) {
                $q->whereHas('roles', fn ($role) => $role->where('name', $filters['role']));
            })
            ->when(!empty($filters['training_center_id']), function ($q) use ($filters) {
                $q->where('training_center_id', (int) $filters['training_center_id']);
            })
            ->when(!empty($filters['training_supervisor_id']), function ($q) use ($filters) {
                $q->where('training_supervisor_id', (int) $filters['training_supervisor_id']);
            })
            ->when(!empty($filters['created_from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['created_from']))
            ->when(!empty($filters['created_to']), fn ($q) => $q->whereDate('created_at', '<=', $filters['created_to']))
            ->with([
                'roles:id,name',
                'permissions:id,name',
                'trainingCenter:id,name',
                'trainingSupervisor:id,name,code,type',
                'governorate:id,name_ar',
                'branch:id,name',
            ])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function getAccessProfile(User $target): User
    {
        return $target->load([
            'roles:id,name,guard_name',
            'permissions:id,name,guard_name',
            'trainingCenter:id,name',
            'trainingSupervisor:id,name,code,type',
            'governorate:id,name_ar',
            'branch:id,name',
        ]);
    }

    public function assignRole(User $actor, User $target, string $roleName, ?Request $request = null): User
    {
        AccessControlGuard::assertParentCanManage($actor, $target);

        if (!AccessControlGuard::canAssignProtectedRole($actor, $roleName)) {
            throw ValidationException::withMessages([
                'role' => ['لا يمكنك إعطاء هذا الدور.'],
            ]);
        }

        // قاعدة الأب: لا يمنح دوراً لا يملك صلاحية تفويضه
        if (!AccessControlGuard::isNationalAdministrator($actor)
            && !AccessControlGuard::parentCanDelegateRole($actor, $roleName)) {
            throw ValidationException::withMessages([
                'role' => ['لا يمكنك منح دور أعلى من صلاحياتك.'],
            ]);
        }

        $role = Role::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->where('name', $roleName)
            ->first();

        if (!$role) {
            throw ValidationException::withMessages(['role' => ['الدور غير موجود.']]);
        }

        if ($target->hasRole($roleName)) {
            throw ValidationException::withMessages(['role' => ['المستخدم يملك هذا الدور مسبقاً.']]);
        }

        $target->assignRole($role);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log('user_role_assigned', $actor, $target, null, [
            'role' => $roleName,
        ], [
            'target_user_id' => $target->id,
            'role_id' => $role->id,
        ], $request);

        return $this->getAccessProfile($target);
    }

    public function revokeRole(User $actor, User $target, string $roleName, ?Request $request = null): User
    {
        $role = Role::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->where('name', $roleName)
            ->firstOrFail();

        AccessControlGuard::assertParentCanManage($actor, $target);
        AccessControlGuard::assertCanRevokeRole($actor, $target, $role);

        if (!$target->hasRole($roleName)) {
            throw ValidationException::withMessages(['role' => ['المستخدم لا يملك هذا الدور.']]);
        }

        $target->removeRole($role);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log('user_role_revoked', $actor, $target, [
            'role' => $roleName,
        ], null, [
            'target_user_id' => $target->id,
            'role_id' => $role->id,
        ], $request);

        return $this->getAccessProfile($target);
    }

    public function assignPermission(User $actor, User $target, string $permissionName, ?Request $request = null): User
    {
        AccessControlGuard::assertParentCanManage($actor, $target);

        // قاعدة الأب: لا يمنح صلاحية لا يملكها
        if (!AccessControlGuard::parentCanDelegatePermission($actor, $permissionName)) {
            throw ValidationException::withMessages([
                'permission' => ['لا يمكنك منح صلاحية لا تملكها.'],
            ]);
        }

        $permission = Permission::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->where('name', $permissionName)
            ->first();

        if (!$permission) {
            throw ValidationException::withMessages(['permission' => ['الصلاحية غير موجودة.']]);
        }

        if ($target->hasDirectPermission($permissionName)) {
            throw ValidationException::withMessages(['permission' => ['المستخدم يملك هذه الصلاحية مباشرة مسبقاً.']]);
        }

        $target->givePermissionTo($permission);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log('user_permission_assigned', $actor, $target, null, [
            'permission' => $permissionName,
        ], [
            'target_user_id' => $target->id,
            'permission_id' => $permission->id,
        ], $request);

        return $this->getAccessProfile($target);
    }

    public function revokePermission(User $actor, User $target, string $permissionName, ?Request $request = null): User
    {
        AccessControlGuard::assertParentCanManage($actor, $target);

        $permission = Permission::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->where('name', $permissionName)
            ->firstOrFail();

        if (!$target->hasDirectPermission($permissionName)) {
            throw ValidationException::withMessages(['permission' => ['المستخدم لا يملك هذه الصلاحية مباشرة.']]);
        }

        $target->revokePermissionTo($permission);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log('user_permission_revoked', $actor, $target, [
            'permission' => $permissionName,
        ], null, [
            'target_user_id' => $target->id,
            'permission_id' => $permission->id,
        ], $request);

        return $this->getAccessProfile($target);
    }

    public function updateStatus(User $actor, User $target, bool $isActive, ?Request $request = null): User
    {
        AccessControlGuard::assertParentCanManage($actor, $target);

        if (!$isActive && AccessControlGuard::userHasAdminRole($target)) {
            $remainingAdmins = User::query()
                ->where('is_active', true)
                ->where('id', '!=', $target->id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', AccessControlGuard::PROTECTED_ROLES))
                ->count();

            if ($remainingAdmins < 1) {
                throw ValidationException::withMessages([
                    'is_active' => ['لا يمكن تعطيل آخر مدير نظام نشط.'],
                ]);
            }
        }

        $before = ['is_active' => (bool) $target->is_active];
        $target->update(['is_active' => $isActive]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log(
            $isActive ? 'user_activated' : 'user_deactivated',
            $actor,
            $target,
            $before,
            ['is_active' => $isActive],
            ['target_user_id' => $target->id],
            $request
        );

        return $this->getAccessProfile($target->fresh());
    }

    public function createUser(User $actor, array $data, ?Request $request = null): User
    {
        $roleName = $data['role'];

        // Admin: يحتاج permission عادية
        // غير Admin (أب): يكفي أن يكون لديه دور في الهرمية يسمح له
        $isAdmin = AccessControlGuard::isNationalAdministrator($actor);

        if ($isAdmin && !$actor->can('create', User::class)) {
            throw ValidationException::withMessages(['user' => ['غير مصرح بإنشاء المستخدمين.']]);
        }

        if (!$isAdmin && !AccessControlGuard::parentCanDelegateRole($actor, $roleName)) {
            throw ValidationException::withMessages(['role' => ['لا يمكنك إنشاء مستخدم بهذا الدور. يتجاوز صلاحياتك.']]);
        }

        if (!AccessControlGuard::canAssignProtectedRole($actor, $roleName)) {
            throw ValidationException::withMessages(['role' => ['لا يمكنك إعطاء هذا الدور.']]);
        }

        // التحقق من الصلاحيات المطلوب منحها
        if (!empty($data['permissions'])) {
            foreach ($data['permissions'] as $perm) {
                if (!AccessControlGuard::parentCanDelegatePermission($actor, $perm)) {
                    throw ValidationException::withMessages([
                        'permissions' => ['لا يمكنك منح صلاحية لا تملكها: ' . $perm],
                    ]);
                }
            }
        }

        $branchScope = $this->resolveUserBranchScope($roleName, $data);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'entity_type' => $this->resolveEntityType($roleName),
            'governorate_id' => $branchScope['governorate_id'],
            'branch_id' => $branchScope['branch_id'],
            'training_center_id' => $data['training_center_id'] ?? null,
            'training_supervisor_id' => $data['training_supervisor_id'] ?? null,
            'trainer_id' => $data['trainer_id'] ?? null,
            'trainee_id' => $data['trainee_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'parent_user_id' => $actor->id,
        ]);

        $user->syncRoles([$roleName]);

        if (!empty($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log(
            'user_created',
            $actor,
            $user,
            null,
            ['email' => $user->email, 'role' => $roleName],
            ['target_user_id' => $user->id],
            $request,
            'users',
            'إنشاء مستخدم جديد'
        );

        return $this->getAccessProfile($user->fresh());
    }

    public function updateUser(User $actor, User $target, array $data, ?Request $request = null): User
    {
        if (AccessControlGuard::isAccessAdministrator($actor)) {
            AccessControlGuard::assertCanModifyUserAccess($actor, $target);
        } else {
            AccessControlGuard::assertParentCanManage($actor, $target);
        }

        $before = $target->only(['name', 'email', 'phone', 'is_active', 'governorate_id', 'branch_id', 'training_center_id', 'training_supervisor_id', 'trainer_id', 'trainee_id']);

        if (array_key_exists('is_active', $data) && !$data['is_active']) {
            $this->assertCanDeactivate($target);
        }

        $updateData = array_intersect_key($data, array_flip([
            'name', 'email', 'phone', 'is_active', 'governorate_id', 'branch_id', 'training_center_id', 'trainer_id', 'trainee_id',
            'training_supervisor_id',
        ]));

        if (array_key_exists('governorate_id', $updateData) || array_key_exists('branch_id', $updateData)) {
            $roleName = $target->getRoleNames()->first() ?? $target->entity_type;
            $scoped = $this->resolveUserBranchScope($roleName, array_merge($target->only([
                'governorate_id', 'branch_id', 'training_center_id', 'trainer_id', 'trainee_id',
            ]), $updateData));
            $updateData['governorate_id'] = $scoped['governorate_id'];
            $updateData['branch_id'] = $scoped['branch_id'];
        }

        $target->update($updateData);

        $this->auditLog->log(
            'user_updated',
            $actor,
            $target,
            $before,
            $target->only(['name', 'email', 'phone', 'is_active', 'governorate_id', 'branch_id', 'training_center_id', 'training_supervisor_id', 'trainer_id', 'trainee_id']),
            ['target_user_id' => $target->id],
            $request,
            'users',
            'تعديل بيانات مستخدم'
        );

        return $this->getAccessProfile($target->fresh());
    }

    public function changePassword(User $actor, User $target, string $password, ?Request $request = null): User
    {
        if (AccessControlGuard::isAccessAdministrator($actor)) {
            AccessControlGuard::assertCanModifyUserAccess($actor, $target);
        } else {
            AccessControlGuard::assertParentCanManage($actor, $target);
        }

        $target->update(['password' => $password]);

        $this->auditLog->log(
            'user_password_changed',
            $actor,
            $target,
            null,
            null,
            ['target_user_id' => $target->id],
            $request,
            'users',
            'تغيير كلمة مرور مستخدم'
        );

        return $this->getAccessProfile($target->fresh());
    }

    public function syncRoles(User $actor, User $target, array $roleNames, array $scopeData = [], ?Request $request = null): User
    {
        AccessControlGuard::assertParentCanManage($actor, $target);

        foreach ($roleNames as $roleName) {
            if (!AccessControlGuard::canAssignProtectedRole($actor, $roleName)) {
                throw ValidationException::withMessages(['roles' => ['لا يمكنك إعطاء دور: ' . $roleName]]);
            }
            if (!AccessControlGuard::isNationalAdministrator($actor)
                && !AccessControlGuard::parentCanDelegateRole($actor, $roleName)) {
                throw ValidationException::withMessages(['roles' => ['لا يمكنك منح دور أعلى من صلاحياتك: ' . $roleName]]);
            }
        }

        $before = $target->getRoleNames()->values()->all();
        $this->assertAdminRoleSafety($target, $before, $roleNames);

        $roles = Role::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->whereIn('name', $roleNames)
            ->get();

        $target->syncRoles($roles);

        $branchBefore = $target->only(['governorate_id', 'branch_id']);
        $branchScope = $this->resolveBranchScopeAfterRoleSync($target, $roleNames, $scopeData);
        if ($branchScope !== null) {
            $target->update([
                'governorate_id' => $branchScope['governorate_id'],
                'branch_id' => $branchScope['branch_id'],
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log(
            'user_roles_synced',
            $actor,
            $target,
            ['roles' => $before, 'branch' => $branchBefore],
            ['roles' => $roleNames, 'branch' => $branchScope ?? $branchBefore],
            ['target_user_id' => $target->id],
            $request,
            'roles',
            'مزامنة أدوار المستخدم'
        );

        return $this->getAccessProfile($target->fresh());
    }

    public function syncDirectPermissions(User $actor, User $target, array $permissionNames, ?Request $request = null): User
    {
        AccessControlGuard::assertParentCanManage($actor, $target);

        // تحقق أن الأب يملك كل صلاحية يريد منحها
        if (!AccessControlGuard::isNationalAdministrator($actor)) {
            foreach ($permissionNames as $perm) {
                if (!AccessControlGuard::parentCanDelegatePermission($actor, $perm)) {
                    throw ValidationException::withMessages([
                        'permissions' => ['لا يمكنك منح صلاحية لا تملكها: ' . $perm],
                    ]);
                }
            }
        }

        $before = $target->permissions->pluck('name')->values()->all();

        $permissions = Permission::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->whereIn('name', $permissionNames)
            ->get();

        $target->syncPermissions($permissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log(
            'user_permissions_synced',
            $actor,
            $target,
            ['permissions' => $before],
            ['permissions' => $permissionNames],
            ['target_user_id' => $target->id],
            $request,
            'permissions',
            'مزامنة صلاحيات المستخدم المباشرة'
        );

        return $this->getAccessProfile($target->fresh());
    }

    private function resolveEntityType(string $roleName): string
    {
        return match ($roleName) {
            'admin', 'super_admin', 'general_director', 'system_admin' => 'admin',
            'deputy_general_director', 'deputy_director' => 'deputy_director',
            'branch_manager' => 'branch_manager',
            'training_manager' => 'training_manager',
            'training_supervisor' => 'training_supervisor',
            'center_user' => 'center_user',
            'trainer_user' => 'trainer_user',
            'trainee_user' => 'trainee_user',
            'auditor' => 'auditor',
            'project_services_manager' => 'project_services_manager',
            'governor' => 'governor',
            'data_entry' => 'data_entry',
            'data_reviewer' => 'data_reviewer',
            default => $roleName,
        };
    }

    /** @return array{governorate_id: int|null, branch_id: int|null} */
    private function resolveUserBranchScope(string $roleName, array $data): array
    {
        $nationalRoles = [
            'general_director', 'admin', 'super_admin', 'deputy_general_director',
            'deputy_director', 'system_admin', 'training_manager', 'auditor',
            'project_services_manager',
        ];

        if (in_array($roleName, $nationalRoles, true)) {
            return ['governorate_id' => null, 'branch_id' => null];
        }

        if ($roleName === 'governor') {
            $governorateId = $data['governorate_id'] ?? null;
            if (! $governorateId) {
                throw ValidationException::withMessages([
                    'governorate_id' => ['يجب اختيار المحافظة لحساب المحافظ.'],
                ]);
            }

            return ['governorate_id' => (int) $governorateId, 'branch_id' => null];
        }

        if (in_array($roleName, ['data_entry', 'data_reviewer', 'branch_officer'], true)) {
            $branchId = $data['branch_id'] ?? null;
            $governorateId = $data['governorate_id'] ?? null;
            if (! $branchId || ! $governorateId) {
                throw ValidationException::withMessages([
                    'branch_id' => ['يجب اختيار المحافظة والفرع لهذا الحساب.'],
                ]);
            }
            $branch = Branch::query()
                ->whereKey($branchId)
                ->where('governorate_id', $governorateId)
                ->first();
            if (! $branch) {
                throw ValidationException::withMessages([
                    'branch_id' => ['الفرع المحدد لا يتبع المحافظة المختارة.'],
                ]);
            }

            return ['governorate_id' => (int) $governorateId, 'branch_id' => (int) $branchId];
        }

        if ($roleName === 'branch_manager') {
            $branchId = $data['branch_id'] ?? null;
            $governorateId = $data['governorate_id'] ?? null;

            if (!$branchId || !$governorateId) {
                throw ValidationException::withMessages([
                    'branch_id' => ['يجب اختيار المحافظة والفرع لمدير الفرع.'],
                ]);
            }

            $branch = Branch::query()
                ->whereKey($branchId)
                ->where('governorate_id', $governorateId)
                ->first();

            if (!$branch) {
                throw ValidationException::withMessages([
                    'branch_id' => ['الفرع المحدد لا يتبع المحافظة المختارة.'],
                ]);
            }

            return ['governorate_id' => (int) $governorateId, 'branch_id' => (int) $branchId];
        }

        if ($roleName === 'training_supervisor') {
            $branchId = $data['branch_id'] ?? null;
            $governorateId = $data['governorate_id'] ?? null;

            if ($branchId && $governorateId) {
                $branch = Branch::query()
                    ->whereKey($branchId)
                    ->where('governorate_id', $governorateId)
                    ->first();

                if (!$branch) {
                    throw ValidationException::withMessages([
                        'branch_id' => ['الفرع المحدد لا يتبع المحافظة المختارة.'],
                    ]);
                }
            }

            return [
                'governorate_id' => $governorateId ? (int) $governorateId : null,
                'branch_id' => $branchId ? (int) $branchId : null,
            ];
        }

        $branchId = isset($data['branch_id']) ? (int) $data['branch_id'] : null;
        $governorateId = isset($data['governorate_id']) ? (int) $data['governorate_id'] : null;

        if ($roleName === 'center_user' && !empty($data['training_center_id'])) {
            $center = TrainingCenter::query()->find($data['training_center_id']);
            $branchId = $branchId ?: ($center?->branch_id ? (int) $center->branch_id : null);
            $governorateId = $governorateId ?: ($center?->governorate_id ? (int) $center->governorate_id : null);
        }

        if ($roleName === 'trainer_user' && !empty($data['trainer_id'])) {
            $trainer = Trainer::query()->find($data['trainer_id']);
            $branchId = $branchId ?: ($trainer?->branch_id ? (int) $trainer->branch_id : null);
            $governorateId = $governorateId ?: ($trainer?->governorate_id ? (int) $trainer->governorate_id : null);
        }

        if ($roleName === 'trainee_user' && !empty($data['trainee_id'])) {
            $trainee = Trainee::query()->find($data['trainee_id']);
            $branchId = $branchId ?: ($trainee?->branch_id ? (int) $trainee->branch_id : null);
            $governorateId = $governorateId ?: ($trainee?->governorate_id ? (int) $trainee->governorate_id : null);
        }

        return ['governorate_id' => $governorateId, 'branch_id' => $branchId];
    }

    /** @param list<string> $roleNames @return array{governorate_id: int|null, branch_id: int|null}|null */
    private function resolveBranchScopeAfterRoleSync(User $target, array $roleNames, array $scopeData): ?array
    {
        $nationalRoles = [
            'general_director', 'admin', 'super_admin', 'deputy_general_director',
            'deputy_director', 'system_admin', 'training_manager', 'auditor',
        ];

        $branchScopedRoles = ['branch_manager', 'training_supervisor', 'center_user', 'trainer_user', 'trainee_user'];

        if (in_array('branch_manager', $roleNames, true)) {
            return $this->resolveUserBranchScope('branch_manager', array_merge(
                $target->only(['governorate_id', 'branch_id', 'training_center_id', 'trainer_id', 'trainee_id']),
                $scopeData
            ));
        }

        $needsBranch = !empty(array_intersect($roleNames, $branchScopedRoles));

        if (!$needsBranch && !empty(array_intersect($roleNames, $nationalRoles))) {
            return ['governorate_id' => null, 'branch_id' => null];
        }

        if (!$needsBranch) {
            return null;
        }

        $primaryRole = collect($roleNames)->first(fn ($r) => in_array($r, $branchScopedRoles, true))
            ?? $roleNames[0];

        return $this->resolveUserBranchScope($primaryRole, array_merge(
            $target->only(['governorate_id', 'branch_id', 'training_center_id', 'trainer_id', 'trainee_id']),
            $scopeData
        ));
    }

    private function assertCanDeactivate(User $target): void
    {
        if (!AccessControlGuard::userHasAdminRole($target)) {
            return;
        }

        $remainingAdmins = User::query()
            ->where('is_active', true)
            ->where('id', '!=', $target->id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', AccessControlGuard::PROTECTED_ROLES))
            ->count();

        if ($remainingAdmins < 1) {
            throw ValidationException::withMessages([
                'is_active' => ['لا يمكن تعطيل آخر مدير نظام نشط.'],
            ]);
        }
    }

    /** @param list<string> $before @param list<string> $after */
    private function assertAdminRoleSafety(User $target, array $before, array $after): void
    {
        $hadProtected = !empty(array_intersect($before, AccessControlGuard::PROTECTED_ROLES));
        $hasProtected = !empty(array_intersect($after, AccessControlGuard::PROTECTED_ROLES));

        if ($hadProtected && !$hasProtected) {
            $remaining = User::query()
                ->where('id', '!=', $target->id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', AccessControlGuard::PROTECTED_ROLES))
                ->count();

            if ($remaining < 1) {
                throw ValidationException::withMessages([
                    'roles' => ['لا يمكن إزالة دور المدير من آخر مدير نظام.'],
                ]);
            }
        }
    }

    // ══════════════════════════════════════════════════════════════
    // Parent-Child management
    // ══════════════════════════════════════════════════════════════

    /**
     * قائمة الأبناء المباشرين للمستخدم.
     */
    public function listChildren(User $parent, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()
            ->where('parent_user_id', $parent->id)
            ->when(!empty($filters['search']), fn ($q) => $q->where(function ($n) use ($filters) {
                $n->where('name', 'like', '%'.$filters['search'].'%')
                  ->orWhere('email', 'like', '%'.$filters['search'].'%');
            }))
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', fn ($q) =>
                $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)))
            ->with([
                'roles:id,name',
                'permissions:id,name',
                'children:id,parent_user_id',
                'governorate:id,name_ar',
                'branch:id,name',
            ])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * ما يمكن للأب منحه (للعرض في الـ UI).
     */
    public function getDelegatableOptions(User $parent): array
    {
        return [
            'roles'       => AccessControlGuard::getDelegatableRoles($parent),
            'permissions' => AccessControlGuard::getDelegatablePermissions($parent),
        ];
    }

    /**
     * نقل ابن لأب آخر (admin فقط).
     */
    public function reassignParent(User $actor, User $target, ?int $newParentId, ?Request $request = null): User
    {
        if (!AccessControlGuard::isNationalAdministrator($actor)) {
            throw ValidationException::withMessages(['user' => ['فقط المدير العام يمكنه إعادة تخصيص الأب.']]);
        }

        $before = ['parent_user_id' => $target->parent_user_id];
        $target->update(['parent_user_id' => $newParentId]);

        $this->auditLog->log('user_parent_reassigned', $actor, $target, $before,
            ['parent_user_id' => $newParentId], ['target_user_id' => $target->id], $request);

        return $this->getAccessProfile($target->fresh());
    }

    public function accessSummary(): array
    {
        $roles = Role::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->withCount('users', 'permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'users_count' => $r->users_count,
                'permissions_count' => $r->permissions_count,
            ]);

        $permissions = Permission::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->withCount('roles')
            ->orderBy('name')
            ->get()
            ->map(fn (Permission $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'roles_count' => $p->roles_count,
            ]);

        $recentLogs = \App\Models\AuditLog::query()
            ->whereIn('action', [
                'role_created', 'role_updated', 'role_deleted', 'role_permissions_updated',
                'permission_created', 'permission_updated', 'permission_deleted',
                'user_role_assigned', 'user_role_revoked',
                'user_permission_assigned', 'user_permission_revoked',
            ])
            ->latest('id')
            ->limit(15)
            ->get(['id', 'user_id', 'action', 'metadata', 'created_at']);

        return [
            'users_count' => User::query()->count(),
            'roles_count' => Role::query()->where('guard_name', AccessControlGuard::GUARD)->count(),
            'permissions_count' => Permission::query()->where('guard_name', AccessControlGuard::GUARD)->count(),
            'admin_users_count' => AccessControlGuard::adminUserCount(),
            'recent_access_logs' => $recentLogs,
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }
}
