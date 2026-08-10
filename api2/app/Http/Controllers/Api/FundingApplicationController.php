<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FundingApplicationResource;
use App\Models\FundedLoan;
use App\Models\FundingApplication;
use App\Services\Finance\FundingApplicationService;
use App\Support\FinanceDataScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FundingApplicationController extends Controller
{
    public function __construct(private FundingApplicationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FundingApplication::class);

        // قائمة خفيفة فقط: بدون details/assignments (كانت تبطّئ الاستجابة بشدة)
        $rows = FinanceDataScope::scopeApplications(
            FundingApplication::query()
                ->select([
                    'id',
                    'application_number',
                    'applicant_user_id',
                    'applicant_name',
                    'phone',
                    'email',
                    'project_name',
                    'project_type',
                    'project_sector',
                    'project_size',
                    'business_stage',
                    'project_status',
                    'requested_amount',
                    'currency',
                    'financing_type',
                    'financing_mode',
                    'repayment_period_months',
                    'status',
                    'current_stage',
                    'submitted_at',
                    'branch_id',
                    'governorate_id',
                    'created_at',
                    'updated_at',
                ])
                ->with([
                    'branch:id,name',
                    'governorate:id,name_ar',
                ]),
            $request->user()
        )
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('governorate_id'), fn ($q) => $q->where('governorate_id', $request->integer('governorate_id')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim((string) $request->string('q'));
                if ($term === '') {
                    return;
                }
                $like = '%'.$term.'%';
                $q->where(function ($inner) use ($like) {
                    $inner->where('application_number', 'like', $like)
                        ->orWhere('project_name', 'like', $like)
                        ->orWhere('applicant_name', 'like', $like);
                });
            })
            ->orderByDesc('id')
            ->paginate(max(1, min((int) $request->integer('per_page', 20), 100)));

        return FundingApplicationResource::collection($rows)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', FundingApplication::class);

        $validated = $request->validate([
            'applicant_name' => ['required', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'project_name' => ['required', 'string', 'max:255'],
            'project_type' => ['nullable', 'string', 'max:100'],
            'project_sector' => ['nullable', 'string', 'max:100'],
            'project_size' => ['nullable', 'in:micro,small,medium'],
            'business_stage' => ['nullable', 'in:idea,startup,existing,expansion'],
            'project_status' => ['nullable', 'in:existing,new'],
            'requested_amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'financing_type' => ['nullable', 'in:capital,working_capital,mixed'],
            'financing_mode' => ['nullable', 'in:islamic,conventional,both'],
            'repayment_period_months' => ['nullable', 'integer', 'min:1'],
            'purpose' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'details' => ['nullable', 'array'],
            'details.owner_experience' => ['nullable', 'string'],
            'details.employees_count' => ['nullable', 'integer', 'min:0'],
            'details.monthly_revenue' => ['nullable', 'numeric', 'min:0'],
            'details.monthly_expenses' => ['nullable', 'numeric', 'min:0'],
            'details.existing_debts' => ['nullable', 'numeric', 'min:0'],
            'details.assets_description' => ['nullable', 'string'],
            'details.market_description' => ['nullable', 'string'],
            'details.challenges' => ['nullable', 'string'],
            'details.requested_support' => ['nullable', 'string'],
            'details.notes' => ['nullable', 'string'],
            'details.extra_data' => ['nullable', 'array'],
        ]);

        $application = $this->service->create($request->user(), $validated, $request);

        return response()->json([
            'message' => 'تم حفظ طلب التمويل.',
            'data' => new FundingApplicationResource($application),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $with = $request->boolean('summary')
            ? ['branch:id,name', 'governorate:id,name_ar']
            : ['details', 'documents', 'consultantAssignments', 'partnerAssignments', 'fundedLoans', 'branch', 'governorate'];

        $application = FundingApplication::query()
            ->with($with)
            ->findOrFail($id);

        $this->authorize('view', $application);

        return response()->json(['data' => new FundingApplicationResource($application)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $application = FundingApplication::query()->findOrFail($id);
        $this->authorize('update', $application);

        $validated = $request->validate([
            'applicant_name' => ['sometimes', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'project_name' => ['sometimes', 'string', 'max:255'],
            'project_type' => ['nullable', 'string', 'max:100'],
            'project_sector' => ['nullable', 'string', 'max:100'],
            'project_size' => ['nullable', 'in:micro,small,medium'],
            'business_stage' => ['nullable', 'in:idea,startup,existing,expansion'],
            'project_status' => ['nullable', 'in:existing,new'],
            'requested_amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'financing_type' => ['nullable', 'in:capital,working_capital,mixed'],
            'financing_mode' => ['nullable', 'in:islamic,conventional,both'],
            'repayment_period_months' => ['nullable', 'integer', 'min:1'],
            'purpose' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'details' => ['nullable', 'array'],
            'details.owner_experience' => ['nullable', 'string'],
            'details.employees_count' => ['nullable', 'integer', 'min:0'],
            'details.monthly_revenue' => ['nullable', 'numeric', 'min:0'],
            'details.monthly_expenses' => ['nullable', 'numeric', 'min:0'],
            'details.existing_debts' => ['nullable', 'numeric', 'min:0'],
            'details.assets_description' => ['nullable', 'string'],
            'details.market_description' => ['nullable', 'string'],
            'details.challenges' => ['nullable', 'string'],
            'details.requested_support' => ['nullable', 'string'],
            'details.notes' => ['nullable', 'string'],
            'details.extra_data' => ['nullable', 'array'],
        ]);

        $application = $this->service->update($application, $request->user(), $validated, $request);

        return response()->json([
            'message' => 'تم تحديث طلب التمويل.',
            'data' => new FundingApplicationResource($application),
        ]);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $application = FundingApplication::query()->findOrFail($id);
        $this->authorize('submit', $application);

        $application = $this->service->submit($application, $request->user(), $request);

        return response()->json([
            'message' => 'تم إرسال طلب التمويل.',
            'data' => new FundingApplicationResource($application),
        ]);
    }

    public function requestCompletion(Request $request, int $id): JsonResponse
    {
        $application = FundingApplication::query()->findOrFail($id);
        $this->authorize('reviewBranch', $application);

        $validated = $request->validate(['notes' => ['nullable', 'string']]);
        $application = $this->service->branchReview($application, $request->user(), 'needs_completion', $validated['notes'] ?? null, $request);

        return response()->json([
            'message' => 'تم طلب استكمال الطلب.',
            'data' => new FundingApplicationResource($application),
        ]);
    }

    public function branchReview(Request $request, int $id): JsonResponse
    {
        $application = FundingApplication::query()->findOrFail($id);
        $this->authorize('reviewBranch', $application);

        $validated = $request->validate([
            'decision' => ['required', 'in:approve,needs_completion,reject,review'],
            'notes' => ['nullable', 'string'],
        ]);

        $application = $this->service->branchReview(
            $application,
            $request->user(),
            $validated['decision'],
            $validated['notes'] ?? null,
            $request
        );

        return response()->json([
            'message' => 'تمت مراجعة الطلب.',
            'data' => new FundingApplicationResource($application),
        ]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $application = FundingApplication::query()->findOrFail($id);
        $this->authorize('approve', $application);

        $application = $this->service->approve($application, $request->user(), $request);

        return response()->json([
            'message' => 'تم اعتماد طلب التمويل.',
            'data' => new FundingApplicationResource($application),
        ]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $application = FundingApplication::query()->findOrFail($id);
        $this->authorize('reject', $application);

        $validated = $request->validate(['notes' => ['nullable', 'string']]);
        $application = $this->service->reject($application, $request->user(), $validated['notes'] ?? null, $request);

        return response()->json([
            'message' => 'تم رفض طلب التمويل.',
            'data' => new FundingApplicationResource($application),
        ]);
    }

    public function assignConsultant(Request $request, int $id): JsonResponse
    {
        $application = FundingApplication::query()->findOrFail($id);
        $this->authorize('assignConsultant', $application);

        $validated = $request->validate([
            'consultant_office_id' => ['required', 'integer', 'exists:consultant_offices,id'],
        ]);

        $assignment = $this->service->assignConsultant(
            $application,
            $request->user(),
            (int) $validated['consultant_office_id'],
            $request
        );

        return response()->json([
            'message' => 'تم إحالة الطلب للمكتب الاستشاري.',
            'data' => $assignment->load('office'),
        ], 201);
    }

    public function assignPartner(Request $request, int $id): JsonResponse
    {
        $application = FundingApplication::query()->findOrFail($id);
        $this->authorize('assignPartner', $application);

        $validated = $request->validate([
            'funding_partner_id' => ['required', 'integer', 'exists:funding_partners,id'],
        ]);

        $assignment = $this->service->assignPartner(
            $application,
            $request->user(),
            (int) $validated['funding_partner_id'],
            $request
        );

        return response()->json([
            'message' => 'تم إحالة الطلب لجهة التمويل.',
            'data' => $assignment->load('partner'),
        ], 201);
    }

    public function createLoan(Request $request, int $id): JsonResponse
    {
        $application = FundingApplication::query()->findOrFail($id);
        $this->authorize('view', $application);
        $this->authorize('create', FundedLoan::class);

        $validated = $request->validate([
            'funding_partner_id' => ['nullable', 'integer', 'exists:funding_partners,id'],
            'approved_amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'interest_type' => ['nullable', 'in:interest,free,profit_margin'],
            'interest_rate' => ['nullable', 'numeric', 'min:0'],
            'profit_margin' => ['nullable', 'numeric', 'min:0'],
            'installment_count' => ['nullable', 'integer', 'min:1'],
            'installment_amount' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $loan = $this->service->createLoan($application, $request->user(), $validated, $request);

        return response()->json([
            'message' => 'تم إنشاء القرض الممول.',
            'data' => $loan->load(['application', 'partner']),
        ], 201);
    }
}
