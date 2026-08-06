<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultingRequest;
use App\Models\ConsultingOffer;
use App\Models\ConsultingContract;
use App\Models\ConsultingRequestAttachment;
use App\Services\Consulting\ConsultingDashboardService;
use App\Services\StatusHistoryService;
use App\Support\ConsultingDataScope;
use App\Support\StatusTransitionValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

class ConsultingRequestController extends Controller
{
    public function __construct(
        private ConsultingDashboardService $dashboard,
        private StatusTransitionValidator $statusValidator,
        private StatusHistoryService $statusHistory,
    ) {}

    private function scopeForUser(Request $request)
    {
        return ConsultingDataScope::scopeForUser(
            ConsultingRequest::query()->with([
                'user:id,name,email',
                'governorate:id,name_ar',
                'branch:id,name',
            ]),
            $request->user()
        );
    }

    /* ── GET /consulting/requests ── */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ConsultingRequest::class);

        $rows = $this->scopeForUser($request)
            ->when($request->filled('status'),       fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category_code'),fn ($q) => $q->where('category_code', $request->string('category_code')))
            ->when($request->filled('governorate_id'),fn ($q) => $q->where('governorate_id', $request->integer('governorate_id')))
            ->orderByDesc('id')
            ->paginate(max(1, min($request->integer('per_page', 20), 100)));

        return response()->json($rows);
    }

    /* ── GET /consulting/requests/stats ── */
    public function stats(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ConsultingRequest::class);

        $data = $this->dashboard->stats(
            $request->user(),
            fn () => $this->scopeForUser($request)
        );

        return response()->json(['data' => $data]);
    }

    /* ── POST /consulting/requests ── */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ConsultingRequest::class);

        $validated = $request->validate([
            'category_code'          => ['required', 'string', 'max:10', 'exists:consulting_categories,code'],
            'request_type'           => ['required', 'in:new_project,existing,financing,classification'],
            'title'                  => ['required', 'string', 'max:255'],
            'description'            => ['required', 'string'],
            'project_name'           => ['nullable', 'string', 'max:255'],
            'economic_activity'      => ['nullable', 'string', 'max:255'],
            'isic4_code'             => ['nullable', 'string', 'max:10'],
            'governorate_id'         => ['nullable', 'integer', 'exists:governorates,id'],
            'budget_min'             => ['nullable', 'numeric', 'min:0'],
            'budget_max'             => ['nullable', 'numeric', 'min:0'],
            'expected_duration_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $user = $request->user();

        // إسناد الفرع تلقائياً من بيانات المستخدم
        $branchId      = $user->branch_id ?? null;
        $governorateId = $validated['governorate_id'] ?? $user->governorate_id ?? null;

        $req = DB::transaction(function () use ($validated, $user, $branchId, $governorateId): ConsultingRequest {
            $row = ConsultingRequest::query()->create(array_merge($validated, [
                'user_id' => $user->id,
                'branch_id' => $branchId,
                'governorate_id' => $governorateId,
                'status' => 'draft',
            ]));

            $this->statusHistory->record($row, null, (string) $row->status, (int) $user->id);

            return $row;
        });

        return response()->json([
            'message' => 'تم حفظ الطلب كمسودة.',
            'data'    => $req,
        ], 201);
    }

    /* ── GET /consulting/requests/{id} ── */
    public function show(Request $request, int $id): JsonResponse
    {
        $req = ConsultingRequest::query()
            ->with([
                'user:id,name,email',
                'governorate:id,name_ar',
                'branch:id,name',
                'attachments',
                'offers.office:id,name,overall_rating',
                'contract.report',
                'contract.review',
            ])
            ->findOrFail($id);

        $this->authorize('view', $req);

        return response()->json(['data' => $req]);
    }

    /* ── PUT /consulting/requests/{id} ── */
    public function update(Request $request, int $id): JsonResponse
    {
        $req = ConsultingRequest::findOrFail($id);
        $this->authorize('update', $req);

        $user = $request->user();
        $isManager = $user->hasRole([
            'admin', 'super_admin', 'system_admin', 'general_director', 'project_services_manager',
        ]);

        if (!$isManager && !in_array($req->status, ['draft', 'needs_info'], true)) {
            return response()->json(['message' => 'لا يمكن تعديل الطلب في حالته الحالية.'], 422);
        }

        $rules = [
            'category_code'          => ['sometimes', 'string', 'max:10'],
            'request_type'           => ['sometimes', 'in:new_project,existing,financing,classification'],
            'title'                  => ['sometimes', 'string', 'max:255'],
            'description'            => ['sometimes', 'string'],
            'project_name'           => ['nullable', 'string', 'max:255'],
            'economic_activity'      => ['nullable', 'string', 'max:255'],
            'isic4_code'             => ['nullable', 'string', 'max:10'],
            'governorate_id'         => ['nullable', 'integer', 'exists:governorates,id'],
            'budget_min'             => ['nullable', 'numeric', 'min:0'],
            'budget_max'             => ['nullable', 'numeric', 'min:0'],
            'expected_duration_days' => ['nullable', 'integer', 'min:1'],
        ];

        if ($isManager) {
            $rules['status'] = ['sometimes', 'string', 'max:50'];
            $rules['branch_notes'] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);
        $req->update($validated);

        return response()->json(['message' => 'تم تحديث الطلب.', 'data' => $req->fresh()]);
    }

    /* ── DELETE /consulting/requests/{id} ── */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $req = ConsultingRequest::findOrFail($id);
        $this->authorize('delete', $req);
        $req->delete();

        return response()->json(['message' => 'تم حذف طلب الاستشارة.']);
    }

    /* ── POST /consulting/requests/{id}/submit ── */
    public function submit(Request $request, int $id): JsonResponse
    {
        $req = ConsultingRequest::findOrFail($id);
        $this->authorize('update', $req);

        $req = DB::transaction(function () use ($req, $request): ConsultingRequest {
            $locked = ConsultingRequest::query()->whereKey($req->id)->lockForUpdate()->firstOrFail();

            $this->statusValidator->assertAllowed(ConsultingRequest::class, (string) $locked->status, 'submitted');

            $from = (string) $locked->status;
            $locked->update(['status' => 'submitted', 'submitted_at' => now()]);
            $this->statusHistory->record($locked, $from, 'submitted', (int) $request->user()->id);

            return $locked->fresh();
        });

        NotificationService::consultingRequestSubmitted($req->id, $req->request_code, $req->title, $req->governorate_id ?? 0);

        return response()->json(['message' => 'تم إرسال الطلب بنجاح.', 'data' => $req]);
    }

    /* ── POST /consulting/requests/{id}/sort ── (مدير الفرع) ── */
    public function sort(Request $request, int $id): JsonResponse
    {
        $req  = ConsultingRequest::findOrFail($id);
        $user = $request->user();

        $this->authorize('sort', $req);

        $validated = $request->validate([
            'action'       => ['required', 'in:approve,needs_info'],
            'branch_notes' => ['nullable', 'string'],
        ]);

        $newStatus = $validated['action'] === 'approve' ? 'awaiting_offers' : 'needs_info';

        $req = DB::transaction(function () use ($req, $validated, $newStatus, $user): ConsultingRequest {
            $locked = ConsultingRequest::query()->whereKey($req->id)->lockForUpdate()->firstOrFail();
            $from = (string) $locked->status;
            $this->statusValidator->assertAllowed(ConsultingRequest::class, $from, $newStatus);

            $locked->update([
                'status' => $newStatus,
                'branch_notes' => $validated['branch_notes'] ?? $locked->branch_notes,
                'branch_manager_id' => $user->id,
                'offers_deadline' => $newStatus === 'awaiting_offers' ? now()->addDays(5) : null,
            ]);

            $this->statusHistory->record($locked, $from, $newStatus, (int) $user->id, $validated['branch_notes'] ?? null);

            return $locked->fresh();
        });

        NotificationService::consultingRequestSorted($req->user_id, $req->id, $req->request_code, $validated['action']);

        if ($newStatus === 'awaiting_offers') {
            NotificationService::consultingNewRequestForOffices($req->id, $req->request_code, $req->category_code);
        }

        return response()->json(['message' => 'تم تحديث حالة الطلب.', 'data' => $req]);
    }

    /* ── POST /consulting/requests/{id}/accept-offer ── */
    public function acceptOffer(Request $request, int $id): JsonResponse
    {
        $req  = ConsultingRequest::with('offers.office')->findOrFail($id);
        $user = $request->user();

        $this->authorize('acceptOffer', $req);

        $validated = $request->validate(['offer_id' => ['required', 'integer', 'exists:consulting_offers,id']]);

        $offer = ConsultingOffer::where('request_id', $req->id)->findOrFail($validated['offer_id']);

        $req = DB::transaction(function () use ($req, $offer, $user) {
            $locked = ConsultingRequest::query()->whereKey($req->id)->lockForUpdate()->firstOrFail();
            $from = (string) $locked->status;
            $this->statusValidator->assertAllowed(ConsultingRequest::class, $from, 'in_progress');

            // رفض بقية العروض
            ConsultingOffer::where('request_id', $req->id)
                ->where('id', '!=', $offer->id)
                ->update(['status' => 'rejected']);

            $offer->update(['status' => 'accepted']);

            // إنشاء العقد
            $contract = ConsultingContract::create([
                'request_id'    => $req->id,
                'offer_id'      => $offer->id,
                'office_id'     => $offer->office_id,
                'client_user_id'=> $user->id,
                'total_value'   => $offer->price,
                'start_date'    => now()->toDateString(),
                'expected_end_date' => now()->addDays($offer->proposed_duration_days)->toDateString(),
            ]);

            $locked->update(['status' => 'in_progress']);
            $this->statusHistory->record($locked, $from, 'in_progress', (int) $user->id);

            return $locked->fresh();
        });

        // إشعار المكتب الفائز + المكاتب المرفوضة
        NotificationService::consultingOfferAccepted($offer->office->user_id ?? 0, $req->id, $req->request_code);
        ConsultingOffer::where('request_id', $req->id)->where('status', 'rejected')->with('office:id,user_id')->each(function ($o) use ($req) {
            if ($o->office?->user_id) {
                NotificationService::consultingOfferRejected($o->office->user_id, $req->id, $req->request_code);
            }
        });

        return response()->json(['message' => 'تم قبول العرض وإنشاء العقد.', 'data' => $req->fresh()]);
    }

    /* ── POST /consulting/requests/{id}/transfer ── */
    public function transfer(Request $request, int $id): JsonResponse
    {
        $req  = ConsultingRequest::findOrFail($id);
        $this->authorize('transfer', $req);

        $validated = $request->validate([
            'target' => ['required', 'in:financing,training,incubation,gis'],
        ]);

        $statusMap = [
            'financing'  => 'transferred_financing',
            'training'   => 'transferred_training',
            'incubation' => 'transferred_incubation',
            'gis'        => 'transferred_gis',
        ];

        $toStatus = $statusMap[$validated['target']];

        $req = DB::transaction(function () use ($req, $toStatus, $request): ConsultingRequest {
            $locked = ConsultingRequest::query()->whereKey($req->id)->lockForUpdate()->firstOrFail();
            $from = (string) $locked->status;
            $this->statusValidator->assertAllowed(ConsultingRequest::class, $from, $toStatus);
            $locked->update(['status' => $toStatus]);
            $this->statusHistory->record($locked, $from, $toStatus, (int) $request->user()->id);

            return $locked->fresh();
        });

        return response()->json(['message' => 'تم التحويل.', 'data' => $req]);
    }

    /* ── Attachment upload ── */
    public function uploadAttachment(Request $request, int $id): JsonResponse
    {
        $req = ConsultingRequest::findOrFail($id);
        $this->authorize('update', $req);

        $request->validate([
            'file'  => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png,xlsx,xls'],
            'stage' => ['nullable', 'in:request,execution,report'],
        ]);

        $file = $request->file('file');
        $path = $file->store("consulting/{$req->id}", 'public');

        $att = ConsultingRequestAttachment::create([
            'request_id'  => $req->id,
            'uploader_id' => $request->user()->id,
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $path,
            'file_type'   => $file->getMimeType(),
            'file_size'   => $file->getSize(),
            'upload_stage'=> $request->input('stage', 'request'),
        ]);

        return response()->json(['message' => 'تم رفع الملف.', 'data' => $att], 201);
    }
}
