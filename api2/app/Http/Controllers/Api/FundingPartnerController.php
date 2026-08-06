<?php



namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;

use App\Models\FundedLoan;

use App\Models\FundingPartner;

use App\Models\FundingPartnerAssignment;

use App\Support\InstitutionalPartnerScope;

use App\Services\AuditLogService;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;



class FundingPartnerController extends Controller

{

    public function __construct(private AuditLogService $auditLog) {}



    public function centralBankDashboard(Request $request): JsonResponse

    {

        $user = $request->user();

        if ($user->hasRole('auditor') || (!$user->hasPermissionTo('finance.central_bank.dashboard') && !$user->hasRole(['general_director', 'admin', 'super_admin']))) {

            abort(403);

        }



        $partnersQuery = InstitutionalPartnerScope::scopeFundingPartners(FundingPartner::query(), $user);



        return response()->json([

            'data' => [

                'total_partners' => (clone $partnersQuery)->count(),

                'pending_partners' => (clone $partnersQuery)->where('status', 'pending')->count(),

                'active_partners' => (clone $partnersQuery)->whereIn('status', ['approved', 'active'])->count(),

                'total_assignments' => FundingPartnerAssignment::query()->count(),

                'approved_decisions' => FundingPartnerAssignment::query()->where('status', 'approved')->count(),

                'total_loans' => FundedLoan::query()->count(),

            ],

        ]);

    }



    public function index(Request $request): JsonResponse

    {

        $this->authorize('viewAny', FundingPartner::class);



        $query = InstitutionalPartnerScope::scopeFundingPartners(FundingPartner::query(), $request->user());



        $rows = $query

            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))

            ->orderBy('name')

            ->paginate(max(1, min((int) $request->integer('per_page', 50), 100)));



        return response()->json($rows);

    }



    public function store(Request $request): JsonResponse

    {

        $this->authorize('create', FundingPartner::class);



        $validated = $request->validate([

            'name' => ['required', 'string', 'max:255'],

            'partner_type' => ['nullable', 'in:bank,fund,guarantee_company,donor,other'],

            'license_number' => ['nullable', 'string', 'max:100'],

            'contact_person' => ['nullable', 'string', 'max:255'],

            'phone' => ['nullable', 'string', 'max:50'],

            'email' => ['nullable', 'email', 'max:255'],

            'status' => ['nullable', 'in:pending,approved,active,inactive,suspended,rejected'],

        ]);



        $user = $request->user();

        $defaultStatus = $user->hasRole(['general_director', 'admin', 'super_admin']) ? 'active' : 'pending';



        $partner = FundingPartner::query()->create(array_merge($validated, [

            'created_by' => $user->id,

            'status' => $validated['status'] ?? $defaultStatus,

            'partner_type' => $validated['partner_type'] ?? 'bank',

            'supervised_by_type' => 'central_bank',

        ]));



        $this->auditLog->log('funding_partner_created', $user, $partner, null, $partner->toArray(), null, $request, 'finance', 'إنشاء جهة تمويل');



        return response()->json(['message' => 'تم إنشاء جهة التمويل.', 'data' => $partner], 201);

    }



    public function show(Request $request, int $id): JsonResponse

    {

        $partner = FundingPartner::query()->findOrFail($id);

        $this->authorize('view', $partner);



        return response()->json(['data' => $partner->load(['approver'])]);

    }



    public function update(Request $request, int $id): JsonResponse

    {

        $partner = FundingPartner::query()->findOrFail($id);

        $this->authorize('update', $partner);



        $validated = $request->validate([

            'name' => ['sometimes', 'string', 'max:255'],

            'partner_type' => ['nullable', 'in:bank,fund,guarantee_company,donor,other'],

            'license_number' => ['nullable', 'string', 'max:100'],

            'contact_person' => ['nullable', 'string', 'max:255'],

            'phone' => ['nullable', 'string', 'max:50'],

            'email' => ['nullable', 'email', 'max:255'],

            'status' => ['nullable', 'in:pending,approved,active,inactive,suspended,rejected'],

        ]);



        $before = $partner->toArray();

        $partner->update(array_merge($validated, ['updated_by' => $request->user()->id]));



        $this->auditLog->log('funding_partner_updated', $request->user(), $partner, $before, $partner->fresh()->toArray(), null, $request, 'finance', 'تحديث جهة تمويل');



        return response()->json(['message' => 'تم تحديث جهة التمويل.', 'data' => $partner->fresh()]);

    }



    public function approvePartner(Request $request, int $id): JsonResponse

    {

        $partner = FundingPartner::query()->findOrFail($id);

        $this->authorize('approve', $partner);



        $partner->update([

            'status' => 'approved',

            'approved_by' => $request->user()->id,

            'approved_at' => now(),

            'updated_by' => $request->user()->id,

        ]);



        $this->auditLog->log('funding_partner_approved', $request->user(), $partner, null, $partner->only(['status', 'approved_by', 'approved_at']), null, $request, 'finance', 'اعتماد جهة تمويل');



        return response()->json(['message' => 'تم اعتماد جهة التمويل.', 'data' => $partner->fresh()]);

    }



    public function activatePartner(Request $request, int $id): JsonResponse

    {

        $partner = FundingPartner::query()->findOrFail($id);

        $this->authorize('activate', $partner);



        if (!in_array($partner->status, ['approved', 'active', 'inactive', 'suspended'], true)) {

            abort(422, 'يجب اعتماد جهة التمويل قبل التفعيل.');

        }



        $partner->update(['status' => 'active', 'updated_by' => $request->user()->id]);

        $this->auditLog->log('funding_partner_activated', $request->user(), $partner, null, ['status' => 'active'], null, $request, 'finance', 'تفعيل جهة تمويل');



        return response()->json(['message' => 'تم تفعيل جهة التمويل.', 'data' => $partner->fresh()]);

    }



    public function suspendPartner(Request $request, int $id): JsonResponse

    {

        $partner = FundingPartner::query()->findOrFail($id);

        $this->authorize('suspend', $partner);



        $partner->update(['status' => 'suspended', 'updated_by' => $request->user()->id]);

        $this->auditLog->log('funding_partner_suspended', $request->user(), $partner, null, ['status' => 'suspended'], null, $request, 'finance', 'تعليق جهة تمويل');



        return response()->json(['message' => 'تم تعليق جهة التمويل.', 'data' => $partner->fresh()]);

    }



    public function partnerAssignments(Request $request, int $id): JsonResponse

    {

        $partner = FundingPartner::query()->findOrFail($id);

        $this->authorize('view', $partner);



        $rows = $partner->assignments()->with('application')->latest('id')->paginate(50);



        return response()->json($rows);

    }



    public function partnerDecisions(Request $request, int $id): JsonResponse

    {

        $partner = FundingPartner::query()->findOrFail($id);

        $this->authorize('view', $partner);



        if (!$request->user()->hasPermissionTo('finance.partner_decisions.view_all') && !$request->user()->hasRole(['general_director', 'admin', 'super_admin', 'central_bank_admin'])) {

            abort(403);

        }



        $rows = $partner->assignments()

            ->whereNotNull('decision_at')

            ->with('application')

            ->latest('decision_at')

            ->paginate(50);



        return response()->json($rows);

    }



    public function partnerLoans(Request $request, int $id): JsonResponse

    {

        $partner = FundingPartner::query()->findOrFail($id);

        $this->authorize('view', $partner);



        $rows = $partner->loans()->with('application')->latest('id')->paginate(50);



        return response()->json($rows);

    }



    public function partnerMetrics(Request $request, int $id): JsonResponse

    {

        $partner = FundingPartner::query()->findOrFail($id);

        $this->authorize('monitor', FundingPartner::class);

        $this->authorize('view', $partner);



        return response()->json([

            'data' => [

                'partner_id' => $partner->id,

                'assignments_total' => $partner->assignments()->count(),

                'decisions_approved' => $partner->assignments()->where('status', 'approved')->count(),

                'decisions_rejected' => $partner->assignments()->where('status', 'rejected')->count(),

                'loans_total' => $partner->loans()->count(),

                'loans_active' => $partner->loans()->where('status', 'active')->count(),

            ],

        ]);

    }



    public function partnerDashboard(Request $request): JsonResponse

    {

        $user = $request->user();

        if (!$user->hasRole('funding_partner') || !$user->funding_partner_id) {

            abort(403, 'لوحة شريك التمويل متاحة لحسابات شركاء التمويل فقط.');

        }



        $assignments = FundingPartnerAssignment::query()

            ->where('funding_partner_id', $user->funding_partner_id);



        return response()->json([

            'data' => [

                'partner_id' => $user->funding_partner_id,

                'assignments_total' => (clone $assignments)->count(),

                'under_review' => (clone $assignments)->whereIn('status', ['sent', 'under_review'])->count(),

                'approved' => (clone $assignments)->where('status', 'approved')->count(),

                'rejected' => (clone $assignments)->where('status', 'rejected')->count(),

                'funded' => (clone $assignments)->where('status', 'funded')->count(),

                'loans_total' => \App\Support\FinanceDataScope::scopeLoans(FundedLoan::query(), $user)->count(),

            ],

        ]);

    }



    public function myAssignments(Request $request): JsonResponse

    {

        $user = $request->user();

        if (!$user->hasRole('funding_partner') || !$user->funding_partner_id) {

            abort(403);

        }



        $rows = FundingPartnerAssignment::query()

            ->where('funding_partner_id', $user->funding_partner_id)

            ->with('application')

            ->latest('id')

            ->paginate(50);



        return response()->json($rows);

    }



    public function decision(Request $request, int $id): JsonResponse

    {

        $assignment = FundingPartnerAssignment::query()->with('application')->findOrFail($id);

        $user = $request->user();



        if ($user->hasRole('auditor')) {

            abort(403);

        }



        if ($user->hasRole('funding_partner') && (int) $user->funding_partner_id !== (int) $assignment->funding_partner_id) {

            abort(403);

        }



        if (!$user->hasPermissionTo('finance.partner_assignments.decide')

            && !$user->hasPermissionTo('finance.partners.decide')

            && !$user->hasRole(['funding_partner', 'general_director', 'admin', 'super_admin'])) {

            abort(403);

        }



        $validated = $request->validate([

            'decision' => ['required', 'in:approved,rejected,under_review,funded'],

            'approved_amount' => ['nullable', 'numeric', 'min:0'],

            'approved_currency' => ['nullable', 'string', 'max:8'],

            'decision_notes' => ['nullable', 'string'],

        ]);



        $assignment->update([

            'status' => $validated['decision'],

            'approved_amount' => $validated['approved_amount'] ?? null,

            'approved_currency' => $validated['approved_currency'] ?? 'SYP',

            'decision_notes' => $validated['decision_notes'] ?? null,

            'decision_at' => now(),

        ]);



        if ($validated['decision'] === 'approved') {

            $assignment->application?->update(['status' => 'approved', 'current_stage' => 'approved']);

        } elseif ($validated['decision'] === 'rejected') {

            $assignment->application?->update(['status' => 'rejected', 'current_stage' => 'rejected']);

        }



        $this->auditLog->log('funding_partner_decision_submitted', $user, $assignment->application, null, $assignment->toArray(), null, $request, 'finance', 'قرار جهة تمويل');

        $this->auditLog->log('finance_partner_decision_submitted', $user, $assignment->application, null, $assignment->toArray(), null, $request, 'finance', 'قرار جهة تمويل');



        return response()->json(['message' => 'تم تسجيل قرار جهة التمويل.', 'data' => $assignment->fresh()]);

    }

}

