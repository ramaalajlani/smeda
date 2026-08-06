<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultingOffer;
use App\Models\ConsultingOffice;
use App\Models\ConsultingRequest;
use App\Services\StatusHistoryService;
use App\Support\StatusTransitionValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class ConsultingOfferController extends Controller
{
    public function __construct(
        private StatusTransitionValidator $statusValidator,
        private StatusHistoryService $statusHistory,
    ) {}

    /* ── GET /consulting/requests/{id}/offers ── */
    public function index(Request $request, int $requestId): JsonResponse
    {
        $req  = ConsultingRequest::findOrFail($requestId);
        $user = $request->user();

        $this->authorize('view', $req);

        $offers = ConsultingOffer::where('request_id', $requestId)
            ->with(['office:id,name,overall_rating,total_requests_completed'])
            ->orderByDesc('id')
            ->get();

        // تعليم "شوهد" إذا الطالب هو من يفتح
        if ($req->user_id === $user->id) {
            ConsultingOffer::where('request_id', $requestId)
                ->whereNull('seen_by_client_at')
                ->update(['seen_by_client_at' => now()]);
        }

        return response()->json(['data' => $offers]);
    }

    /* ── POST /consulting/requests/{id}/offers ── (المكتب يقدم عرض) ── */
    public function store(Request $request, int $requestId): JsonResponse
    {
        $req  = ConsultingRequest::findOrFail($requestId);
        $user = $request->user();

        if ($req->status !== 'awaiting_offers') {
            return response()->json(['message' => 'الطلب لا يقبل عروضاً في الوقت الحالي.'], 422);
        }

        $office = ConsultingOffice::where('user_id', $user->id)->where('status', 'active')->firstOrFail();

        if ((int) ($office->governorate_id ?? 0) > 0
            && (int) ($req->governorate_id ?? 0) > 0
            && (int) $office->governorate_id !== (int) $req->governorate_id) {
            abort(403);
        }

        if (!$office->specializations()->where('category_code', $req->category_code)->exists()) {
            abort(403);
        }

        // تحقق أن المكتب لم يقدم عرضاً بالفعل
        if (ConsultingOffer::where('request_id', $requestId)->where('office_id', $office->id)->exists()) {
            return response()->json(['message' => 'قدّمت عرضاً لهذا الطلب مسبقاً.'], 422);
        }

        $validated = $request->validate([
            'methodology_text'       => ['required', 'string'],
            'proposed_duration_days' => ['required', 'integer', 'min:1'],
            'price'                  => ['required', 'numeric', 'min:0'],
            'sample_attachments'     => ['nullable', 'string'],
        ]);

        $offer = DB::transaction(function () use ($validated, $requestId, $office, $req, $user): ConsultingOffer {
            $created = ConsultingOffer::create(array_merge($validated, [
                'request_id' => $requestId,
                'office_id' => $office->id,
                'status' => 'pending',
                'submitted_at' => now(),
            ]));

            $locked = ConsultingRequest::query()->whereKey($req->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'awaiting_offers') {
                $from = (string) $locked->status;
                $this->statusValidator->assertAllowed(ConsultingRequest::class, $from, 'offer_submitted');
                $locked->update(['status' => 'offer_submitted']);
                $this->statusHistory->record($locked, $from, 'offer_submitted', (int) $user->id);
            }

            return $created;
        });

        NotificationService::consultingNewOffer($req->user_id, $req->id, $req->request_code, $office->name);

        return response()->json(['message' => 'تم تقديم العرض بنجاح.', 'data' => $offer], 201);
    }
}
