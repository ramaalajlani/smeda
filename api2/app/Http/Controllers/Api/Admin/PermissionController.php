<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use App\Http\Resources\Admin\PermissionResource;
use App\Services\Admin\PermissionManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(private PermissionManagementService $permissionService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        if ($request->boolean('grouped', true)) {
            return response()->json([
                'data' => $this->permissionService->listGrouped(),
            ]);
        }

        return response()->json([
            'data' => PermissionResource::collection($this->permissionService->listWithStats()),
        ]);
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->permissionService->create(
            $request->validated('name'),
            $request->user(),
            $request
        );

        return response()->json([
            'message' => 'تم إنشاء الصلاحية بنجاح.',
            'data' => new PermissionResource($permission),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $permission = $this->permissionService->findOrFail($id);
        $this->authorize('view', $permission);

        return response()->json([
            'data' => new PermissionResource($permission),
        ]);
    }

    public function update(UpdatePermissionRequest $request, int $id): JsonResponse
    {
        $permission = $this->permissionService->findOrFail($id);
        $permission = $this->permissionService->update(
            $permission,
            $request->validated('name'),
            $request->user(),
            $request
        );

        return response()->json([
            'message' => 'تم تحديث الصلاحية بنجاح.',
            'data' => new PermissionResource($permission),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $permission = $this->permissionService->findOrFail($id);
        $this->authorize('delete', $permission);

        $this->permissionService->delete($permission, $request->user(), $request);

        return response()->json([
            'message' => 'تم حذف الصلاحية بنجاح.',
        ]);
    }
}
