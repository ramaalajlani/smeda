<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Services\AuditLogService;
use App\Support\AccessControlGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleManagementService
{
    public function __construct(private AuditLogService $auditLog) {}

    public function listWithStats(): Collection
    {
        return Role::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->withCount('users', 'permissions')
            ->orderBy('name')
            ->get();
    }

    public function findOrFail(int $id): Role
    {
        return Role::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->with(['permissions:id,name,guard_name'])
            ->withCount('users')
            ->findOrFail($id);
    }

    public function create(string $name, User $actor, ?Request $request = null): Role
    {
        if (AccessControlGuard::isProtectedRole($name)) {
            throw ValidationException::withMessages([
                'name' => ['لا يمكن إنشاء دور محجوز بهذا الاسم.'],
            ]);
        }

        if (Role::query()->where('name', $name)->where('guard_name', AccessControlGuard::GUARD)->exists()) {
            throw ValidationException::withMessages(['name' => ['الدور موجود مسبقاً.']]);
        }

        $role = Role::create(['name' => $name, 'guard_name' => AccessControlGuard::GUARD]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log('role_created', $actor, $role, null, ['name' => $role->name], null, $request);

        return $role;
    }

    public function update(Role $role, string $name, User $actor, ?Request $request = null): Role
    {
        if (AccessControlGuard::isProtectedRole($role->name)) {
            throw ValidationException::withMessages([
                'role' => ['لا يمكن تعديل اسم دور محمي.'],
            ]);
        }

        if (AccessControlGuard::isProtectedRole($name)) {
            throw ValidationException::withMessages([
                'name' => ['لا يمكن استخدام اسم دور محجوز.'],
            ]);
        }

        $old = ['name' => $role->name];
        $role->update(['name' => $name]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log('role_updated', $actor, $role, $old, ['name' => $role->name], null, $request);

        return $role->refresh();
    }

    public function delete(Role $role, User $actor, ?Request $request = null): void
    {
        AccessControlGuard::assertRoleDeletable($role);

        $old = ['name' => $role->name, 'id' => $role->id];
        $role->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log('role_deleted', $actor, null, $old, null, null, $request);
    }

    /**
     * @param list<string> $permissionNames
     */
    public function syncPermissions(Role $role, array $permissionNames, User $actor, ?Request $request = null): Role
    {
        if (AccessControlGuard::isProtectedRole($role->name) && !AccessControlGuard::isAccessAdministrator($actor)) {
            throw ValidationException::withMessages([
                'role' => ['لا يمكن تعديل صلاحيات دور محمي.'],
            ]);
        }

        $permissions = Permission::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->whereIn('name', $permissionNames)
            ->pluck('name')
            ->all();

        if (count($permissions) !== count(array_unique($permissionNames))) {
            throw ValidationException::withMessages([
                'permissions' => ['واحدة أو أكثر من الصلاحيات غير موجودة.'],
            ]);
        }

        $old = $role->permissions()->pluck('name')->sort()->values()->all();
        $role->syncPermissions($permissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log('role_permissions_updated', $actor, $role, [
            'permissions' => $old,
        ], [
            'permissions' => $role->permissions()->pluck('name')->sort()->values()->all(),
        ], null, $request);

        return $role->load('permissions:id,name,guard_name');
    }

    public function attachPermission(Role $role, Permission $permission, User $actor, ?Request $request = null): Role
    {
        return $this->syncPermissions(
            $role,
            array_merge($role->permissions()->pluck('name')->all(), [$permission->name]),
            $actor,
            $request
        );
    }

    public function detachPermission(Role $role, Permission $permission, User $actor, ?Request $request = null): Role
    {
        $remaining = $role->permissions()
            ->where('name', '!=', $permission->name)
            ->pluck('name')
            ->all();

        return $this->syncPermissions($role, $remaining, $actor, $request);
    }
}
