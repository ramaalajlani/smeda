<?php



namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;

use App\Models\Agreement;

use App\Models\Branch;

use App\Services\AuditLogService;

use App\Support\BranchDataScope;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

use Illuminate\Validation\Rule;

use Illuminate\Validation\ValidationException;



class AgreementController extends Controller

{

    public function __construct(private AuditLogService $auditLog) {}



    public function index(Request $request): JsonResponse

    {

        $this->authorize('viewAny', Agreement::class);

        $user = $request->user();



        $rows = Agreement::query()

            ->with(['creator:id,name,email', 'approver:id,name,email', 'branch:id,name', 'governorate:id,name_ar'])

            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))

            ->when($request->filled('scope_type'), fn ($q) => $q->where('scope_type', $request->string('scope_type')))

            ->when(BranchDataScope::isBranchManager($user), fn ($q) => $q

                ->where('scope_type', 'branch')

                ->where('branch_id', $user->branch_id))

            ->orderByDesc('id')

            ->paginate(max(1, min((int) $request->integer('per_page', 20), 100)));



        return response()->json($rows);

    }



    public function store(Request $request): JsonResponse

    {

        $this->authorize('create', Agreement::class);



        $validated = $this->validatePayload($request);

        $row = Agreement::query()->create(array_merge($validated, [

            'created_by' => $request->user()->id,

            'status' => $validated['status'] ?? 'draft',

        ]));



        $this->auditLog->log('agreement_created', $request->user(), $row, null, $row->toArray(), null, $request, 'agreements', 'إنشاء اتفاقية');



        return response()->json(['message' => 'تم إنشاء الاتفاقية.', 'data' => $row->load(['branch', 'governorate'])], 201);

    }



    public function show(Request $request, int $id): JsonResponse

    {

        $row = Agreement::query()

            ->with(['creator:id,name,email', 'approver:id,name,email', 'branch:id,name', 'governorate:id,name_ar'])

            ->findOrFail($id);

        $this->authorize('view', $row);



        return response()->json(['data' => $row]);

    }



    public function update(Request $request, int $id): JsonResponse

    {

        $row = Agreement::query()->findOrFail($id);

        $this->authorize('update', $row);



        $before = $row->toArray();

        $validated = $this->validatePayload($request, true, $row);

        $row->update($validated);

        $this->auditLog->log('agreement_updated', $request->user(), $row, $before, $row->fresh()->toArray(), null, $request, 'agreements', 'تعديل اتفاقية');



        return response()->json(['message' => 'تم تحديث الاتفاقية.', 'data' => $row->fresh()->load(['branch', 'governorate'])]);

    }



    public function approve(Request $request, int $id): JsonResponse

    {

        $row = Agreement::query()->findOrFail($id);

        $this->authorize('approve', $row);



        $before = $row->only(['status', 'approved_by']);

        $row->update([

            'status' => 'active',

            'approved_by' => $request->user()->id,

        ]);



        $this->auditLog->log('agreement_approved', $request->user(), $row, $before, $row->only(['status', 'approved_by']), null, $request, 'agreements', 'اعتماد اتفاقية');



        return response()->json(['message' => 'تم اعتماد الاتفاقية.', 'data' => $row->fresh()]);

    }



    /** @return array<string, mixed> */

    private function validatePayload(Request $request, bool $partial = false, ?Agreement $existing = null): array

    {

        $rules = [

            'title' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],

            'partner_name' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],

            'agreement_type' => ['sometimes', 'nullable', 'string', 'max:100'],

            'scope_type' => ['sometimes', Rule::in(['national', 'branch'])],

            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],

            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],

            'status' => ['sometimes', 'string', 'max:50'],

            'start_date' => ['sometimes', 'nullable', 'date'],

            'end_date' => ['sometimes', 'nullable', 'date'],

            'amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],

            'notes' => ['sometimes', 'nullable', 'string'],

        ];



        $validated = $request->validate($rules);

        $scopeType = $validated['scope_type'] ?? ($existing?->scope_type ?? 'national');



        if ($scopeType === 'national') {

            if (!$partial || array_key_exists('scope_type', $validated)) {

                $validated['scope_type'] = 'national';

                $validated['governorate_id'] = null;

                $validated['branch_id'] = null;

            }

        } else {

            $validated['scope_type'] = 'branch';

            $branchId = $validated['branch_id'] ?? $existing?->branch_id;

            $governorateId = $validated['governorate_id'] ?? $existing?->governorate_id;



            if (!$branchId || !$governorateId) {

                throw ValidationException::withMessages([

                    'branch_id' => ['يجب اختيار المحافظة والفرع للاتفاقية الخاصة بفرع.'],

                ]);

            }



            $branch = Branch::query()->findOrFail((int) $branchId);

            if ((int) $branch->governorate_id !== (int) $governorateId) {

                throw ValidationException::withMessages([

                    'branch_id' => ['الفرع المحدد لا يتبع المحافظة المختارة.'],

                ]);

            }



            $validated['branch_id'] = (int) $branchId;

            $validated['governorate_id'] = (int) $governorateId;

        }



        return $validated;

    }

}

