<?php



namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;

use App\Models\ConsultantAssignment;

use App\Models\ConsultantOffice;

use App\Models\ConsultantReport;

use App\Support\InstitutionalPartnerScope;

use App\Services\AuditLogService;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;



class FundingConsultantController extends Controller

{

    public function __construct(private AuditLogService $auditLog) {}



    public function unionDashboard(Request $request): JsonResponse

    {

        $user = $request->user();

        if ($user->hasRole('auditor') || (!$user->hasPermissionTo('finance.consultant_union.dashboard') && !$user->hasRole(['general_director', 'admin', 'super_admin']))) {

            abort(403);

        }



        $officesQuery = InstitutionalPartnerScope::scopeConsultantOffices(ConsultantOffice::query(), $user);



        return response()->json([

            'data' => [

                'total_offices' => (clone $officesQuery)->count(),

                'pending_offices' => (clone $officesQuery)->where('status', 'pending')->count(),

                'active_offices' => (clone $officesQuery)->whereIn('status', ['approved', 'active'])->count(),

                'total_assignments' => ConsultantAssignment::query()->count(),

                'pending_price_offers' => ConsultantAssignment::query()->where('price_offer_status', 'submitted')->count(),

                'total_reports' => ConsultantReport::query()->count(),

            ],

        ]);

    }



    public function indexOffices(Request $request): JsonResponse

    {

        $this->authorize('viewAny', ConsultantOffice::class);



        $query = InstitutionalPartnerScope::scopeConsultantOffices(ConsultantOffice::query(), $request->user());



        $rows = $query

            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))

            ->orderBy('name')

            ->paginate(max(1, min((int) $request->integer('per_page', 50), 100)));



        return response()->json($rows);

    }



    public function storeOffice(Request $request): JsonResponse

    {

        $this->authorize('create', ConsultantOffice::class);



        $validated = $request->validate([

            'name' => ['required', 'string', 'max:255'],

            'license_number' => ['nullable', 'string', 'max:100'],

            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],

            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],

            'specialization' => ['nullable', 'string', 'max:255'],

            'sectors' => ['nullable', 'array'],

            'contact_person' => ['nullable', 'string', 'max:255'],

            'phone' => ['nullable', 'string', 'max:50'],

            'email' => ['nullable', 'email', 'max:255'],

            'address' => ['nullable', 'string'],

            'status' => ['nullable', 'in:pending,approved,active,inactive,suspended,rejected'],

        ]);



        $user = $request->user();

        $defaultStatus = $user->hasRole(['general_director', 'admin', 'super_admin']) ? 'active' : 'pending';



        $office = ConsultantOffice::query()->create(array_merge($validated, [

            'created_by' => $user->id,

            'status' => $validated['status'] ?? $defaultStatus,

            'supervised_by_type' => 'consultant_union',

        ]));



        $this->auditLog->log('consultant_office_created', $user, $office, null, $office->toArray(), null, $request, 'finance', 'إنشاء مكتب استشاري');



        return response()->json(['message' => 'تم إنشاء المكتب الاستشاري.', 'data' => $office], 201);

    }



    public function showOffice(Request $request, int $id): JsonResponse

    {

        $office = ConsultantOffice::query()->findOrFail($id);

        $this->authorize('view', $office);



        return response()->json(['data' => $office->load(['governorate', 'branch', 'approver'])]);

    }



    public function updateOffice(Request $request, int $id): JsonResponse

    {

        $office = ConsultantOffice::query()->findOrFail($id);

        $this->authorize('update', $office);



        $validated = $request->validate([

            'name' => ['sometimes', 'string', 'max:255'],

            'license_number' => ['nullable', 'string', 'max:100'],

            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],

            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],

            'specialization' => ['nullable', 'string', 'max:255'],

            'sectors' => ['nullable', 'array'],

            'contact_person' => ['nullable', 'string', 'max:255'],

            'phone' => ['nullable', 'string', 'max:50'],

            'email' => ['nullable', 'email', 'max:255'],

            'address' => ['nullable', 'string'],

            'status' => ['nullable', 'in:pending,approved,active,inactive,suspended,rejected'],

        ]);



        $before = $office->toArray();

        $office->update(array_merge($validated, ['updated_by' => $request->user()->id]));



        $this->auditLog->log('consultant_office_updated', $request->user(), $office, $before, $office->fresh()->toArray(), null, $request, 'finance', 'تحديث مكتب استشاري');



        return response()->json(['message' => 'تم تحديث المكتب الاستشاري.', 'data' => $office->fresh()]);

    }



    public function approveOffice(Request $request, int $id): JsonResponse

    {

        $office = ConsultantOffice::query()->findOrFail($id);

        $this->authorize('approve', $office);



        $office->update([

            'status' => 'approved',

            'approved_by' => $request->user()->id,

            'approved_at' => now(),

            'updated_by' => $request->user()->id,

        ]);



        $this->auditLog->log('consultant_office_approved', $request->user(), $office, null, $office->only(['status', 'approved_by', 'approved_at']), null, $request, 'finance', 'اعتماد مكتب استشاري');



        return response()->json(['message' => 'تم اعتماد المكتب الاستشاري.', 'data' => $office->fresh()]);

    }



    public function activateOffice(Request $request, int $id): JsonResponse

    {

        $office = ConsultantOffice::query()->findOrFail($id);

        $this->authorize('activate', $office);



        if (!in_array($office->status, ['approved', 'active', 'inactive', 'suspended'], true)) {

            abort(422, 'يجب اعتماد المكتب قبل التفعيل.');

        }



        $office->update(['status' => 'active', 'updated_by' => $request->user()->id]);

        $this->auditLog->log('consultant_office_activated', $request->user(), $office, null, ['status' => 'active'], null, $request, 'finance', 'تفعيل مكتب استشاري');



        return response()->json(['message' => 'تم تفعيل المكتب الاستشاري.', 'data' => $office->fresh()]);

    }



    public function suspendOffice(Request $request, int $id): JsonResponse

    {

        $office = ConsultantOffice::query()->findOrFail($id);

        $this->authorize('suspend', $office);



        $office->update(['status' => 'suspended', 'updated_by' => $request->user()->id]);

        $this->auditLog->log('consultant_office_suspended', $request->user(), $office, null, ['status' => 'suspended'], null, $request, 'finance', 'تعليق مكتب استشاري');



        return response()->json(['message' => 'تم تعليق المكتب الاستشاري.', 'data' => $office->fresh()]);

    }



    public function officeAssignments(Request $request, int $id): JsonResponse

    {

        $office = ConsultantOffice::query()->findOrFail($id);

        $this->authorize('view', $office);



        $rows = $office->assignments()->with('application')->latest('id')->paginate(50);



        return response()->json($rows);

    }



    public function indexAssignments(Request $request): JsonResponse

    {

        $user = $request->user();

        if (!$user->hasPermissionTo('finance.consultant_union.dashboard')

            && !$user->hasRole(['consultant_union_admin', 'general_director', 'admin', 'super_admin', 'system_admin', 'auditor'])) {

            abort(403);

        }



        $perPage = max(1, min((int) $request->integer('per_page', 30), 100));

        $rows = ConsultantAssignment::query()

            ->with(['application:id,project_name', 'office:id,name'])

            ->latest('id')

            ->paginate($perPage);



        return response()->json($rows);

    }



    public function officeReports(Request $request, int $id): JsonResponse

    {

        $office = ConsultantOffice::query()->findOrFail($id);

        $this->authorize('view', $office);



        if (!$request->user()->hasPermissionTo('finance.consultants.reports.view') && !$request->user()->hasRole(['general_director', 'admin', 'super_admin', 'consultant_union_admin'])) {

            abort(403);

        }



        $rows = $office->reports()->with('application')->latest('id')->paginate(50);



        return response()->json($rows);

    }



    public function officeMetrics(Request $request, int $id): JsonResponse

    {

        $office = ConsultantOffice::query()->findOrFail($id);

        $this->authorize('monitor', ConsultantOffice::class);

        $this->authorize('view', $office);



        return response()->json([

            'data' => [

                'office_id' => $office->id,

                'assignments_total' => $office->assignments()->count(),

                'assignments_active' => $office->assignments()->whereIn('status', ['assigned', 'accepted', 'in_progress'])->count(),

                'reports_total' => $office->reports()->count(),

                'price_offers_submitted' => $office->assignments()->where('price_offer_status', 'submitted')->count(),

            ],

        ]);

    }



    public function officeDashboard(Request $request): JsonResponse

    {

        $user = $request->user();

        if (!$user->hasRole('consultant_office') || !$user->consultant_office_id) {

            abort(403, 'لوحة المكتب الاستشاري متاحة لحسابات المكاتب فقط.');

        }



        $office = ConsultantOffice::query()->findOrFail($user->consultant_office_id);

        $assignments = ConsultantAssignment::query()->where('consultant_office_id', $office->id);



        return response()->json([

            'data' => [

                'office_id' => $office->id,

                'office_name' => $office->name,

                'assignments_total' => (clone $assignments)->count(),

                'assignments_pending' => (clone $assignments)->where('status', 'assigned')->count(),

                'assignments_in_progress' => (clone $assignments)->whereIn('status', ['accepted', 'in_progress'])->count(),

                'assignments_completed' => (clone $assignments)->where('status', 'completed')->count(),

                'assignments_rejected' => (clone $assignments)->where('status', 'rejected')->count(),

                'pending_price_offers' => (clone $assignments)->where('price_offer_status', 'submitted')->count(),

                'reports_total' => $office->reports()->count(),

            ],

        ]);

    }



    public function myAssignments(Request $request): JsonResponse

    {

        $user = $request->user();

        if (!$user->hasRole('consultant_office') || !$user->consultant_office_id) {

            abort(403);

        }



        $rows = ConsultantAssignment::query()

            ->where('consultant_office_id', $user->consultant_office_id)

            ->with('application')

            ->latest('id')

            ->paginate(50);



        return response()->json($rows);

    }



    public function acceptAssignment(Request $request, int $id): JsonResponse

    {

        $assignment = ConsultantAssignment::query()->with('application')->findOrFail($id);

        $this->authorize('accept', $assignment);



        $assignment->update(['status' => 'accepted']);

        $this->auditLog->log('consultant_assignment_accepted', $request->user(), $assignment->application, null, ['status' => 'accepted'], null, $request, 'finance', 'قبول إحالة استشارية');

        $this->auditLog->log('finance_consultant_assignment_accepted', $request->user(), $assignment->application, null, ['status' => 'accepted'], null, $request, 'finance', 'قبول إحالة استشارية');



        return response()->json(['message' => 'تم قبول الإحالة.', 'data' => $assignment->fresh()]);

    }



    public function rejectAssignment(Request $request, int $id): JsonResponse

    {

        $assignment = ConsultantAssignment::query()->with('application')->findOrFail($id);

        $this->authorize('accept', $assignment);



        $validated = $request->validate(['notes' => ['nullable', 'string']]);

        $assignment->update(['status' => 'rejected', 'consultant_notes' => $validated['notes'] ?? null]);

        $this->auditLog->log('consultant_assignment_rejected', $request->user(), $assignment->application, null, ['status' => 'rejected'], null, $request, 'finance', 'رفض إحالة استشارية');

        $this->auditLog->log('finance_consultant_assignment_rejected', $request->user(), $assignment->application, null, ['status' => 'rejected'], null, $request, 'finance', 'رفض إحالة استشارية');



        return response()->json(['message' => 'تم رفض الإحالة.', 'data' => $assignment->fresh()]);

    }



    public function priceOffer(Request $request, int $id): JsonResponse

    {

        $assignment = ConsultantAssignment::query()->with('application')->findOrFail($id);

        $this->authorize('submitPrice', $assignment);



        $validated = $request->validate([

            'price_offer_amount' => ['required', 'numeric', 'min:0'],

            'price_offer_currency' => ['nullable', 'string', 'max:8'],

            'consultant_notes' => ['nullable', 'string'],

        ]);



        $assignment->update(array_merge($validated, [

            'price_offer_status' => 'submitted',

            'status' => 'in_progress',

            'price_offer_currency' => $validated['price_offer_currency'] ?? 'SYP',

        ]));



        $assignment->application?->update(['status' => 'consultant_priced', 'current_stage' => 'consultant_priced']);



        $this->auditLog->log('consultant_price_submitted', $request->user(), $assignment->application, null, $assignment->only(['price_offer_amount', 'price_offer_currency']), null, $request, 'finance', 'تقديم عرض سعر استشاري');

        $this->auditLog->log('finance_consultant_price_submitted', $request->user(), $assignment->application, null, $assignment->only(['price_offer_amount', 'price_offer_currency']), null, $request, 'finance', 'تقديم عرض سعر استشاري');



        return response()->json(['message' => 'تم تقديم عرض السعر.', 'data' => $assignment->fresh()]);

    }



    public function approvePrice(Request $request, int $id): JsonResponse

    {

        $assignment = ConsultantAssignment::query()->with('application')->findOrFail($id);

        $this->authorize('approvePrice', $assignment);



        $assignment->update(['price_offer_status' => 'approved']);

        $this->auditLog->log('finance_consultant_price_approved', $request->user(), $assignment->application, null, ['price_offer_status' => 'approved'], null, $request, 'finance', 'اعتماد عرض سعر استشاري');



        return response()->json(['message' => 'تم اعتماد عرض السعر.', 'data' => $assignment->fresh()]);

    }



    public function storeReport(Request $request): JsonResponse

    {

        $validated = $request->validate([

            'funding_application_id' => ['required', 'integer', 'exists:funding_applications,id'],

            'consultant_office_id' => ['required', 'integer', 'exists:consultant_offices,id'],

            'feasibility_score' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'risk_level' => ['nullable', 'in:low,medium,high'],

            'recommended_amount' => ['nullable', 'numeric', 'min:0'],

            'recommendation' => ['required', 'in:approve,reject,needs_adjustment'],

            'report_summary' => ['nullable', 'string'],

            'strengths' => ['nullable', 'string'],

            'weaknesses' => ['nullable', 'string'],

            'conditions' => ['nullable', 'string'],

        ]);



        $user = $request->user();

        if ($user->hasRole('auditor') || !$user->hasPermissionTo('finance.consultant_reports.create')) {

            if (!$user->hasPermissionTo('finance.consultants.submit_report')) {

                abort(403);

            }

        }



        if ($user->hasRole('consultant_office') && (int) $user->consultant_office_id !== (int) $validated['consultant_office_id']) {

            abort(403);

        }



        $assignment = ConsultantAssignment::query()

            ->where('funding_application_id', $validated['funding_application_id'])

            ->where('consultant_office_id', $validated['consultant_office_id'])

            ->latest('id')

            ->first();



        if (!$assignment) {

            abort(403, 'لا توجد إحالة لهذا الطلب.');

        }



        $report = ConsultantReport::query()->create(array_merge($validated, [

            'consultant_user_id' => $user->id,

        ]));



        $assignment->update(['status' => 'completed', 'completed_at' => now()]);



        $this->auditLog->log('consultant_report_submitted', $user, $report, null, $report->toArray(), null, $request, 'finance', 'رفع تقرير استشاري');

        $this->auditLog->log('finance_consultant_report_submitted', $user, $report, null, $report->toArray(), null, $request, 'finance', 'رفع تقرير استشاري');



        return response()->json(['message' => 'تم رفع التقرير الاستشاري.', 'data' => $report], 201);

    }

}

