<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\SyncRolePermissionsRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Http\Resources\Admin\RoleResource;
use App\Services\Admin\RoleManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private RoleManagementService $roleService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $roles = $this->roleService->listWithStats();

        return response()->json([
            'data' => RoleResource::collection($roles),
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create(
            $request->validated('name'),
            $request->user(),
            $request
        );

        return response()->json([
            'message' => 'تم إنشاء الدور بنجاح.',
            'data' => new RoleResource($role),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->roleService->findOrFail($id);
        $this->authorize('view', $role);

        return response()->json([
            'data' => new RoleResource($role),
        ]);
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $role = $this->roleService->findOrFail($id);
        $role = $this->roleService->update(
            $role,
            $request->validated('name'),
            $request->user(),
            $request
        );

        return response()->json([
            'message' => 'تم تحديث الدور بنجاح.',
            'data' => new RoleResource($role),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $role = $this->roleService->findOrFail($id);
        $this->authorize('delete', $role);

        $this->roleService->delete($role, $request->user(), $request);

        return response()->json([
            'message' => 'تم حذف الدور بنجاح.',
        ]);
    }

    public function syncPermissions(SyncRolePermissionsRequest $request, int $id): JsonResponse
    {
        $role = $this->roleService->findOrFail($id);
        $role = $this->roleService->syncPermissions(
            $role,
            $request->validated('permissions'),
            $request->user(),
            $request
        );

        return response()->json([
            'message' => 'تم تحديث صلاحيات الدور بنجاح.',
            'data' => new RoleResource($role),
        ]);
    }

    public function attachPermission(Request $request, int $id, int $permissionId): JsonResponse
    {
        $role = $this->roleService->findOrFail($id);
        $this->authorize('syncPermissions', $role);

        $permission = Permission::query()->findOrFail($permissionId);
        $role = $this->roleService->attachPermission($role, $permission, $request->user(), $request);

        return response()->json([
            'message' => 'تم ربط الصلاحية بالدور.',
            'data' => new RoleResource($role),
        ]);
    }

    public function detachPermission(Request $request, int $id, int $permissionId): JsonResponse
    {
        $role = $this->roleService->findOrFail($id);
        $this->authorize('syncPermissions', $role);

        $permission = Permission::query()->findOrFail($permissionId);
        $role = $this->roleService->detachPermission($role, $permission, $request->user(), $request);

        return response()->json([
            'message' => 'تم إزالة الصلاحية من الدور.',
            'data' => new RoleResource($role),
        ]);
    }
}
