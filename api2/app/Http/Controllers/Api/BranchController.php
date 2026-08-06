<?php



namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;

use App\Http\Resources\BranchResource;

use App\Models\Branch;

use App\Services\Admin\BranchDashboardService;
use App\Services\Admin\BranchManagementService;

use App\Support\BranchDataScope;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

use Illuminate\Validation\Rule;



class BranchController extends Controller

{

    public function __construct(
        private BranchManagementService $branchService,
        private BranchDashboardService $dashboardService,
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('viewAny', Branch::class);

        if (!$request->filled('branch_id')) {
            if (BranchDataScope::isBranchManager($user) && $user->branch_id) {
                $branchId = (int) $user->branch_id;

                return response()->json([
                    'scope' => 'branch',
                    'data' => $this->dashboardService->detail($branchId),
                ]);
            }

            if (!$this->dashboardService->canViewNationalOverview($user)) {
                abort(422, 'branch_id مطلوب.');
            }

            return response()->json([
                'scope' => 'national',
                'branches' => $this->dashboardService->branchesOverview(),
            ]);
        }

        $branchId = $this->dashboardService->resolveBranchId($user, $request->integer('branch_id'));

        return response()->json([
            'scope' => 'branch',
            'data' => $this->dashboardService->detail($branchId),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('viewAny', Branch::class);

        $lite = $request->boolean('lite');

        $query = Branch::query()
            ->when($request->filled('governorate_id'), fn ($q) => $q->where('governorate_id', $request->integer('governorate_id')))
            ->when(BranchDataScope::isBranchManager($user), fn ($q) => $q->whereKey($user->branch_id))
            ->orderBy('name');

        if ($lite) {
            $branches = $query
                ->select(['id', 'name', 'code', 'governorate_id', 'is_active'])
                ->get();

            return response()->json([
                'data' => $branches->map(fn (Branch $b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                    'code' => $b->code,
                    'governorate_id' => $b->governorate_id,
                    'is_active' => (bool) $b->is_active,
                ])->values(),
            ]);
        }

        $branches = $query
            ->with(['governorate:id,name_ar,code', 'manager:id,name,email'])
            ->withCount(['users'])
            ->get();

        return response()->json(['data' => BranchResource::collection($branches)]);
    }



    public function store(Request $request): JsonResponse

    {

        $this->authorize('create', Branch::class);



        $validated = $request->validate([

            'name' => ['required', 'string', 'max:255'],

            'code' => ['required', 'string', 'max:50', Rule::unique('branches', 'code')],

            'governorate_id' => ['required', 'integer', 'exists:governorates,id'],

            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],

            'is_active' => ['sometimes', 'boolean'],

            'notes' => ['nullable', 'string'],

        ]);



        $branch = $this->branchService->create($request->user(), $validated, $request);

        $branch->setAttribute('has_dependent_data', $this->branchService->hasDependentData($branch));



        return response()->json([

            'message' => 'تم إنشاء الفرع بنجاح.',

            'data' => new BranchResource($branch),

        ], 201);

    }



    public function show(Request $request, int $id): JsonResponse

    {

        $user = $request->user();

        $branch = Branch::query()->with(['governorate', 'manager:id,name,email'])->findOrFail($id);

        $this->authorize('view', $branch);



        if (BranchDataScope::isBranchManager($user) && (int) $user->branch_id !== (int) $branch->id) {

            abort(403);

        }



        $branch->setAttribute('has_dependent_data', $this->branchService->hasDependentData($branch));



        return response()->json(['data' => new BranchResource($branch)]);

    }



    public function update(Request $request, int $id): JsonResponse

    {

        $branch = Branch::query()->findOrFail($id);

        $this->authorize('update', $branch);



        $validated = $request->validate([

            'name' => ['sometimes', 'string', 'max:255'],

            'code' => ['sometimes', 'string', 'max:50', Rule::unique('branches', 'code')->ignore($branch->id)],

            'governorate_id' => ['sometimes', 'integer', 'exists:governorates,id'],

            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],

            'is_active' => ['sometimes', 'boolean'],

            'notes' => ['nullable', 'string'],

        ]);



        if (array_key_exists('is_active', $validated) && $validated['is_active'] === false && $branch->is_active) {

            $branch = $this->branchService->deactivate($request->user(), $branch, $request);

            unset($validated['is_active']);

        } elseif (array_key_exists('is_active', $validated) && $validated['is_active'] === true && !$branch->is_active) {

            $branch = $this->branchService->activate($request->user(), $branch, $request);

            unset($validated['is_active']);

        }



        if ($validated) {

            $branch = $this->branchService->update($request->user(), $branch, $validated, $request);

        }



        $branch->setAttribute('has_dependent_data', $this->branchService->hasDependentData($branch));



        return response()->json([

            'message' => 'تم تحديث الفرع بنجاح.',

            'data' => new BranchResource($branch->load(['governorate:id,name_ar,code', 'manager:id,name,email'])),

        ]);

    }



    public function destroy(Request $request, int $id): JsonResponse

    {

        $branch = Branch::query()->findOrFail($id);

        $this->authorize('delete', $branch);



        if ($this->branchService->hasDependentData($branch)) {

            $branch = $this->branchService->deactivate($request->user(), $branch, $request);

            $branch->setAttribute('has_dependent_data', true);



            return response()->json([

                'message' => 'الفرع عليه بيانات — تم تعطيله بدلاً من الحذف.',

                'data' => new BranchResource($branch),

            ]);

        }



        $this->branchService->deleteOrDeactivate($request->user(), $branch, $request);



        return response()->json(['message' => 'تم حذف الفرع بنجاح.']);

    }

}

