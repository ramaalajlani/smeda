<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FundedLoan;
use App\Models\LoanPayment;
use App\Services\AuditLogService;
use App\Support\FinanceDataScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FundedLoanController extends Controller
{
    public function __construct(private AuditLogService $auditLog) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FundedLoan::class);

        $rows = FinanceDataScope::scopeLoans(
            FundedLoan::query()->with(['application', 'partner']),
            $request->user()
        )
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('funding_partner_id'), fn ($q) => $q->where('funding_partner_id', $request->integer('funding_partner_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('loan_number', 'like', $term)
                        ->orWhereHas('application', fn ($app) => $app->where('project_name', 'like', $term));
                });
            })
            ->orderByDesc('id')
            ->paginate(max(1, min((int) $request->integer('per_page', 20), 100)));

        return response()->json($rows);
    }

    public function stats(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FundedLoan::class);

        $user = $request->user();
        $cacheKey = sprintf(
            'finance:loans:stats:v1:u%d:b%s:r%s',
            $user->id,
            $user->branch_id ?? '0',
            md5($user->getRoleNames()->sort()->implode(','))
        );

        $data = Cache::remember($cacheKey, 90, function () use ($user) {
            $base = FinanceDataScope::scopeLoans(FundedLoan::query(), $user);

            $byStatus = (clone $base)
                ->select('status', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('status')
                ->pluck('aggregate', 'status')
                ->map(fn ($count) => (int) $count)
                ->all();

            return [
                'total' => array_sum($byStatus),
                'active' => $byStatus['active'] ?? 0,
                'defaulted' => $byStatus['defaulted'] ?? 0,
                'closed' => $byStatus['closed'] ?? 0,
                'paid' => $byStatus['paid'] ?? 0,
                'total_funded_amount' => (float) (clone $base)->sum('approved_amount'),
                'by_status' => $byStatus,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $loan = FundedLoan::query()->with(['application', 'partner', 'payments'])->findOrFail($id);
        $this->authorize('view', $loan);

        return response()->json(['data' => $loan]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $loan = FundedLoan::query()->findOrFail($id);
        $this->authorize('update', $loan);

        $validated = $request->validate([
            'installment_count' => ['sometimes', 'integer', 'min:1'],
            'installment_amount' => ['nullable', 'numeric', 'min:0'],
            'end_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:active,paid,defaulted,restructured,closed'],
        ]);

        $loan->update($validated);

        return response()->json(['message' => 'تم تحديث القرض.', 'data' => $loan->fresh()]);
    }

    public function payments(Request $request, int $id): JsonResponse
    {
        $loan = FundedLoan::query()->findOrFail($id);
        $this->authorize('view', $loan);

        return response()->json(['data' => $loan->payments()->orderBy('due_date')->get()]);
    }

    public function storePayment(Request $request, int $id): JsonResponse
    {
        $loan = FundedLoan::query()->findOrFail($id);
        $this->authorize('recordPayment', $loan);

        $validated = $request->validate([
            'due_date' => ['required', 'date'],
            'paid_date' => ['nullable', 'date'],
            'amount_due' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:pending,paid,late,partial,defaulted'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = LoanPayment::query()->create(array_merge($validated, [
            'funded_loan_id' => $loan->id,
            'amount_paid' => $validated['amount_paid'] ?? 0,
            'status' => $validated['status'] ?? 'pending',
        ]));

        $this->auditLog->log('finance_payment_recorded', $request->user(), $loan, null, $payment->toArray(), null, $request, 'finance', 'تسجيل دفعة قرض');

        return response()->json(['message' => 'تم تسجيل الدفعة.', 'data' => $payment], 201);
    }

    public function markDefaulted(Request $request, int $id): JsonResponse
    {
        $loan = FundedLoan::query()->with('application')->findOrFail($id);
        $this->authorize('markDefaulted', $loan);

        $loan->update(['status' => 'defaulted']);
        $loan->application?->update(['status' => 'defaulted', 'current_stage' => 'defaulted']);

        $this->auditLog->log('finance_loan_defaulted', $request->user(), $loan, null, ['status' => 'defaulted'], null, $request, 'finance', 'تعليم قرض كمتعثر');

        return response()->json(['message' => 'تم تعليم القرض كمتعثر.', 'data' => $loan->fresh()]);
    }

    public function close(Request $request, int $id): JsonResponse
    {
        $loan = FundedLoan::query()->with('application')->findOrFail($id);
        $this->authorize('update', $loan);

        $loan->update(['status' => 'closed']);
        $this->auditLog->log('finance_loan_closed', $request->user(), $loan, null, ['status' => 'closed'], null, $request, 'finance', 'إغلاق قرض');

        return response()->json(['message' => 'تم إغلاق القرض.', 'data' => $loan->fresh()]);
    }
}
