<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Services\AuditLogService;
use App\Support\AccessControlGuard;
use App\Support\PermissionModuleMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionManagementService
{
    public function __construct(private AuditLogService $auditLog) {}

    public function listGrouped(): array
    {
        $names = Permission::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->orderBy('name')
            ->pluck('name');

        return PermissionModuleMapper::group($names);
    }

    public function listWithStats(): Collection
    {
        return Permission::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->withCount('roles')
            ->orderBy('name')
            ->get();
    }

    public function findOrFail(int $id): Permission
    {
        return Permission::query()
            ->where('guard_name', AccessControlGuard::GUARD)
            ->withCount('roles')
            ->findOrFail($id);
    }

    public function create(string $name, User $actor, ?Request $request = null): Permission
    {
        $permission = Permission::findOrCreate($name, AccessControlGuard::GUARD);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log('permission_created', $actor, $permission, null, [
            'name' => $permission->name,
        ], null, $request);

        return $permission;
    }

    public function update(Permission $permission, string $name, User $actor, ?Request $request = null): Permission
    {
        if (in_array($permission->name, AccessControlGuard::CORE_PERMISSIONS, true)) {
            throw ValidationException::withMessages([
                'permission' => ['لا يمكن تعديل صلاحية أساسية للنظام.'],
            ]);
        }

        $old = ['name' => $permission->name];
        $permission->update(['name' => $name]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log('permission_updated', $actor, $permission, $old, [
            'name' => $permission->name,
        ], null, $request);

        return $permission->refresh();
    }

    public function delete(Permission $permission, User $actor, ?Request $request = null): void
    {
        AccessControlGuard::assertPermissionDeletable($permission->name);

        if ($permission->roles()->count() > 0 || $permission->users()->count() > 0) {
            throw ValidationException::withMessages([
                'permission' => ['لا يمكن حذف صلاحية مستخدمة في أدوار أو مستخدمين.'],
            ]);
        }

        $old = ['name' => $permission->name, 'id' => $permission->id];
        $permission->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->auditLog->log('permission_deleted', $actor, null, $old, null, null, $request);
    }
}
