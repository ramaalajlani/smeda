<?php



namespace App\Http\Controllers\Api\Admin;



use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\AssignUserPermissionRequest;

use App\Http\Requests\Admin\AssignUserRoleRequest;

use App\Http\Requests\Admin\ChangeUserPasswordRequest;

use App\Http\Requests\Admin\RevokeUserPermissionRequest;

use App\Http\Requests\Admin\RevokeUserRoleRequest;

use App\Http\Requests\Admin\StoreAdminUserRequest;

use App\Http\Requests\Admin\SyncUserPermissionsRequest;

use App\Http\Requests\Admin\SyncUserRolesRequest;

use App\Http\Requests\Admin\UpdateAdminUserRequest;

use App\Http\Requests\Admin\UpdateUserStatusRequest;

use App\Http\Resources\Admin\UserAccessListResource;
use App\Http\Resources\Admin\UserAccessResource;

use App\Models\User;

use App\Services\Admin\UserAccessService;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;



class UserAccessController extends Controller

{

    public function __construct(private UserAccessService $userAccessService) {}



    public function index(Request $request): JsonResponse

    {

        $this->authorize('viewAny', User::class);



        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));



        $users = $this->userAccessService->paginateUsers($perPage, $request->only([

            'search', 'role', 'is_active', 'training_center_id', 'training_supervisor_id', 'created_from', 'created_to',

        ]), $request->user());



        return UserAccessResource::collection($users)->response();

    }



    public function store(StoreAdminUserRequest $request): JsonResponse

    {

        $profile = $this->userAccessService->createUser(

            $request->user(),

            $request->validated(),

            $request

        );



        return response()->json([

            'message' => 'تم إنشاء المستخدم بنجاح.',

            'data' => new UserAccessResource($profile),

        ], 201);

    }



    public function show(int $id): JsonResponse

    {

        $target = User::query()->findOrFail($id);

        $this->authorize('viewAccess', $target);



        return response()->json([

            'data' => new UserAccessResource($this->userAccessService->getAccessProfile($target)),

        ]);

    }



    public function update(UpdateAdminUserRequest $request, int $id): JsonResponse

    {

        $target = User::query()->findOrFail($id);

        $profile = $this->userAccessService->updateUser(

            $request->user(),

            $target,

            $request->validated(),

            $request

        );



        return response()->json([

            'message' => 'تم تحديث المستخدم بنجاح.',

            'data' => new UserAccessResource($profile),

        ]);

    }



    public function changePassword(ChangeUserPasswordRequest $request, int $id): JsonResponse

    {

        $target = User::query()->findOrFail($id);

        $profile = $this->userAccessService->changePassword(

            $request->user(),

            $target,

            $request->validated('password'),

            $request

        );



        return response()->json([

            'message' => 'تم تغيير كلمة المرور بنجاح.',

            'data' => new UserAccessResource($profile),

        ]);

    }



    public function syncRoles(SyncUserRolesRequest $request, int $id): JsonResponse

    {

        $target = User::query()->findOrFail($id);

        $profile = $this->userAccessService->syncRoles(

            $request->user(),

            $target,

            $request->validated('roles'),

            $request->validated(),

            $request

        );



        return response()->json([

            'message' => 'تم تحديث أدوار المستخدم بنجاح.',

            'data' => new UserAccessResource($profile),

        ]);

    }



    public function syncPermissions(SyncUserPermissionsRequest $request, int $id): JsonResponse

    {

        $target = User::query()->findOrFail($id);

        $profile = $this->userAccessService->syncDirectPermissions(

            $request->user(),

            $target,

            $request->validated('permissions'),

            $request

        );



        return response()->json([

            'message' => 'تم تحديث صلاحيات المستخدم بنجاح.',

            'data' => new UserAccessResource($profile),

        ]);

    }



    public function assignRole(AssignUserRoleRequest $request, int $id): JsonResponse

    {

        $target = User::query()->findOrFail($id);

        $profile = $this->userAccessService->assignRole(

            $request->user(),

            $target,

            $request->validated('role'),

            $request

        );



        return response()->json([

            'message' => 'تم إعطاء الدور بنجاح.',

            'data' => new UserAccessResource($profile),

        ]);

    }



    public function revokeRole(RevokeUserRoleRequest $request, int $id, string $role): JsonResponse

    {

        $target = User::query()->findOrFail($id);

        $profile = $this->userAccessService->revokeRole(

            $request->user(),

            $target,

            $role,

            $request

        );



        return response()->json([

            'message' => 'تم سحب الدور بنجاح.',

            'data' => new UserAccessResource($profile),

        ]);

    }



    public function assignPermission(AssignUserPermissionRequest $request, int $id): JsonResponse

    {

        $target = User::query()->findOrFail($id);

        $profile = $this->userAccessService->assignPermission(

            $request->user(),

            $target,

            $request->validated('permission'),

            $request

        );



        return response()->json([

            'message' => 'تم إعطاء الصلاحية بنجاح.',

            'data' => new UserAccessResource($profile),

        ]);

    }



    public function revokePermission(RevokeUserPermissionRequest $request, int $id, string $permission): JsonResponse

    {

        $target = User::query()->findOrFail($id);

        $profile = $this->userAccessService->revokePermission(

            $request->user(),

            $target,

            $permission,

            $request

        );



        return response()->json([

            'message' => 'تم سحب الصلاحية بنجاح.',

            'data' => new UserAccessResource($profile),

        ]);

    }



    public function myChildren(Request $request): JsonResponse
    {
        $parent = $request->user();
        $children = $this->userAccessService->listChildren($parent, $request->only(['search', 'is_active']));

        return response()->json([
            'data' => UserAccessListResource::collection($children),
        ]);
    }

    public function childrenOf(Request $request, int $id): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $parent = User::query()->findOrFail($id);
        $children = $this->userAccessService->listChildren($parent, $request->only(['search', 'is_active']));

        return response()->json([
            'data' => UserAccessListResource::collection($children),
        ]);
    }

    public function delegatableOptions(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->userAccessService->getDelegatableOptions($request->user()),
        ]);
    }

    public function reassignParent(Request $request, int $id): JsonResponse
    {
        $target = User::query()->findOrFail($id);
        $request->validate(['parent_user_id' => ['nullable', 'integer', 'exists:users,id']]);

        $profile = $this->userAccessService->reassignParent(
            $request->user(),
            $target,
            $request->input('parent_user_id'),
            $request
        );

        return response()->json([
            'message' => 'تم تغيير الأب بنجاح.',
            'data' => new UserAccessResource($profile),
        ]);
    }

    public function updateStatus(UpdateUserStatusRequest $request, int $id): JsonResponse

    {

        $target = User::query()->findOrFail($id);

        $profile = $this->userAccessService->updateStatus(

            $request->user(),

            $target,

            (bool) $request->validated('is_active'),

            $request

        );



        return response()->json([

            'message' => $request->boolean('is_active')

                ? 'تم تفعيل المستخدم بنجاح.'

                : 'تم تعطيل المستخدم بنجاح.',

            'data' => new UserAccessResource($profile),

        ]);

    }

}


