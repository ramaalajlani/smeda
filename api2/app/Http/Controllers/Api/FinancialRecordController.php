<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\FinancialRecord;
use App\Services\AuditLogService;
use App\Support\BranchDataScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class FinancialRecordController extends Controller
{
    public function __construct(private AuditLogService $auditLog) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialRecord::class);
        $user = $request->user();

        $rows = FinancialRecord::query()
            ->with(['branch:id,name', 'governorate:id,name_ar', 'creator:id,name,email'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('record_type'), fn ($q) => $q->where('record_type', $request->string('record_type')))
            ->when(BranchDataScope::isBranchManager($user), fn (Builder $q) => $q->where('branch_id', $user->branch_id))
            ->orderByDesc('id')
            ->paginate(max(1, min((int) $request->integer('per_page', 20), 100)));

        return response()->json($rows);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', FinancialRecord::class);

        $validated = $request->validate([
            'record_type' => ['required', 'in:funding,payment,commitment,revenue'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'status' => ['nullable', 'string', 'max:50'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],
            'notes' => ['nullable', 'string'],
        ]);
        $validated = $this->validateBranchScope($validated);

        $row = FinancialRecord::query()->create(array_merge($validated, [
            'created_by' => $request->user()->id,
            'status' => $validated['status'] ?? 'draft',
            'currency' => $validated['currency'] ?? 'SYP',
        ]));

        $this->auditLog->log('financial_record_created', $request->user(), $row, null, $row->toArray(), null, $request, 'finance', 'إنشاء سجل مالي');

        return response()->json(['message' => 'تم إنشاء السجل المالي.', 'data' => $row], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $row = FinancialRecord::query()->with(['branch', 'governorate', 'creator', 'approver'])->findOrFail($id);
        $this->authorize('view', $row);

        return response()->json(['data' => $row]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = FinancialRecord::query()->findOrFail($id);
        $this->authorize('update', $row);

        $before = $row->toArray();
        $validated = $request->validate([
            'record_type' => ['sometimes', 'in:funding,payment,commitment,revenue'],
            'title' => ['sometimes', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'max:8'],
            'status' => ['sometimes', 'string', 'max:50'],
            'branch_id' => ['sometimes', 'nullable', 'integer', 'exists:branches,id'],
            'governorate_id' => ['sometimes', 'nullable', 'integer', 'exists:governorates,id'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);
        $validated = $this->validateBranchScope($validated, $row);

        $row->update($validated);
        $this->auditLog->log('financial_record_updated', $request->user(), $row, $before, $row->fresh()->toArray(), null, $request, 'finance', 'تعديل سجل مالي');

        return response()->json(['message' => 'تم تحديث السجل المالي.', 'data' => $row->fresh()]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $row = FinancialRecord::query()->findOrFail($id);
        $this->authorize('approve', $row);

        $before = $row->only(['status', 'approved_by']);
        $row->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
        ]);

        $this->auditLog->log('financial_record_approved', $request->user(), $row, $before, $row->only(['status', 'approved_by']), null, $request, 'finance', 'اعتماد سجل مالي');

        return response()->json(['message' => 'تم اعتماد السجل المالي.', 'data' => $row->fresh()]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function validateBranchScope(array $validated, ?FinancialRecord $existing = null): array
    {
        $branchId = $validated['branch_id'] ?? $existing?->branch_id;
        $governorateId = $validated['governorate_id'] ?? $existing?->governorate_id;

        if (!$branchId && !$governorateId) {
            return $validated;
        }

        if ($branchId && $governorateId) {
            $branch = Branch::query()->findOrFail((int) $branchId);
            if ((int) $branch->governorate_id !== (int) $governorateId) {
                throw ValidationException::withMessages([
                    'branch_id' => ['الفرع المحدد لا يتبع المحافظة المختارة.'],
                ]);
            }

            $validated['branch_id'] = (int) $branchId;
            $validated['governorate_id'] = (int) $governorateId;

            return $validated;
        }

        throw ValidationException::withMessages([
            'branch_id' => ['يجب تحديد المحافظة والفرع معاً للسجلات المرتبطة بفرع.'],
        ]);
    }
}
