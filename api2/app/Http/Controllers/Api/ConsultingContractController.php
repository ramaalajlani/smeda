<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultingContract;
use App\Models\ConsultingMessage;
use App\Models\ConsultingReport;
use App\Models\ConsultingReview;
use App\Models\ConsultingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class ConsultingContractController extends Controller
{
    private function findContractForUser(int $id, $user): ConsultingContract
    {
        $contract = ConsultingContract::with([
            'request:id,title,status,category_code,request_code',
            'office:id,name,overall_rating',
            'client:id,name,email',
            'report',
            'review',
        ])->findOrFail($id);

        $isAdmin       = $user->hasRole([
            'admin', 'super_admin', 'system_admin', 'general_director',
            'branch_manager', 'branch_officer', 'governor', 'consultant_union_admin',
        ]);
        $isClient      = $contract->client_user_id === $user->id;
        $isOfficeUser  = \App\Models\ConsultingOffice::where('user_id', $user->id)
                           ->where('id', $contract->office_id)->exists();

        if (!$isAdmin && !$isClient && !$isOfficeUser) abort(403);

        return $contract;
    }

    /* ── GET /consulting/contracts/{id} ── */
    public function show(Request $request, int $id): JsonResponse
    {
        $contract = $this->findContractForUser($id, $request->user());
        return response()->json(['data' => $contract]);
    }

    /* ── POST /consulting/contracts/{id}/sign ── */
    public function sign(Request $request, int $id): JsonResponse
    {
        $user     = $request->user();
        $contract = ConsultingContract::findOrFail($id);

        $isClient = $contract->client_user_id === $user->id;
        $isOffice = \App\Models\ConsultingOffice::where('user_id', $user->id)
                      ->where('id', $contract->office_id)->exists();

        if (!$isClient && !$isOffice) abort(403);

        if ($isClient && !$contract->signed_by_client_at) {
            $contract->update(['signed_by_client_at' => now()]);
        } elseif ($isOffice && !$contract->signed_by_office_at) {
            $contract->update(['signed_by_office_at' => now()]);
        }

        return response()->json(['message' => 'تم التوقيع.', 'data' => $contract->fresh()]);
    }

    /* ── GET /consulting/contracts/{id}/messages ── */
    public function messages(Request $request, int $id): JsonResponse
    {
        $contract = $this->findContractForUser($id, $request->user());

        $messages = ConsultingMessage::where('contract_id', $id)
            ->with('sender:id,name')
            ->orderBy('id')
            ->get();

        // تعليم مقروءة
        ConsultingMessage::where('contract_id', $id)
            ->where('sender_id', '!=', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['data' => $messages]);
    }

    /* ── POST /consulting/contracts/{id}/messages ── */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $contract = $this->findContractForUser($id, $request->user());
        $user     = $request->user();

        $validated = $request->validate([
            'message_text'    => ['required', 'string'],
            'attachment_path' => ['nullable', 'string'],
        ]);

        $role = $contract->client_user_id === $user->id ? 'client' : 'office';
        if ($user->hasRole(['admin', 'super_admin'])) $role = 'admin';
        if ($user->hasRole(['branch_manager', 'branch_officer'])) $role = 'branch_manager';

        $msg = ConsultingMessage::create(array_merge($validated, [
            'contract_id' => $id,
            'sender_id'   => $user->id,
            'sender_role' => $role,
            'sent_at'     => now(),
        ]));

        // إشعار الطرف الآخر
        $req = \App\Models\ConsultingRequest::find($id) ?? \App\Models\ConsultingRequest::find($contract->request_id);
        $notifRecipient = $role === 'client' ? (\App\Models\ConsultingOffice::find($contract->office_id)?->user_id) : $contract->client_user_id;
        if ($notifRecipient && $notifRecipient !== $user->id) {
            NotificationService::consultingNewContractMessage($notifRecipient, $contract->request_id, $user->name);
        }

        return response()->json(['message' => 'تم إرسال الرسالة.', 'data' => $msg->load('sender:id,name')], 201);
    }

    /* ── POST /consulting/contracts/{id}/report ── (المكتب يرفع التقرير) ── */
    public function uploadReport(Request $request, int $id): JsonResponse
    {
        $contract = ConsultingContract::findOrFail($id);
        $user     = $request->user();

        $isOffice = \App\Models\ConsultingOffice::where('user_id', $user->id)
                      ->where('id', $contract->office_id)->exists();
        if (!$isOffice && !$user->hasRole(['admin', 'super_admin'])) abort(403);

        $request->validate([
            'file'                   => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'recommendation_type'    => ['nullable', 'in:none,financing,training,incubation,gis,license,activity_change'],
            'recommendation_details' => ['nullable', 'string'],
            'isic4_recommendation'   => ['nullable', 'string', 'max:10'],
        ]);

        $path = $request->file('file')->store("consulting/reports/{$contract->id}", 'public');

        $report = ConsultingReport::updateOrCreate(
            ['contract_id' => $contract->id],
            [
                'request_id'             => $contract->request_id,
                'report_pdf_path'        => $path,
                'submission_date'        => now(),
                'review_status'          => 'pending',
                'recommendation_type'    => $request->input('recommendation_type', 'none'),
                'recommendation_details' => $request->input('recommendation_details'),
                'isic4_recommendation'   => $request->input('isic4_recommendation'),
            ]
        );

        // تحديث حالة الطلب
        ConsultingRequest::where('id', $contract->request_id)
            ->update(['status' => 'report_submitted']);

        $req = \App\Models\ConsultingRequest::find($contract->request_id);
        if ($req) NotificationService::consultingReportSubmitted($req->id, $req->request_code, $req->governorate_id ?? 0);

        return response()->json(['message' => 'تم رفع التقرير. بانتظار اعتماد الهيئة.', 'data' => $report], 201);
    }

    /* ── POST /consulting/contracts/{id}/approve-report ── (الهيئة) ── */
    public function approveReport(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole(['admin', 'super_admin', 'branch_manager'])) abort(403);

        $contract = ConsultingContract::findOrFail($id);
        $report   = ConsultingReport::where('contract_id', $id)->firstOrFail();

        $validated = $request->validate([
            'action'         => ['required', 'in:approve,return'],
            'reviewer_notes' => ['nullable', 'string'],
        ]);

        if ($validated['action'] === 'approve') {
            $report->update([
                'review_status' => 'approved',
                'reviewer_id'   => $user->id,
                'review_date'   => now(),
                'reviewer_notes'=> $validated['reviewer_notes'] ?? null,
            ]);

            ConsultingRequest::where('id', $contract->request_id)
                ->update(['status' => 'completed', 'completed_at' => now()]);

            $contract->update([
                'actual_end_date' => now()->toDateString(),
            ]);

            // تحديث إحصائيات المكتب
            \App\Models\ConsultingOffice::where('id', $contract->office_id)
                ->increment('total_requests_completed');
        } else {
            $report->update([
                'review_status' => 'returned',
                'reviewer_id'   => $user->id,
                'review_date'   => now(),
                'reviewer_notes'=> $validated['reviewer_notes'],
            ]);

            ConsultingRequest::where('id', $contract->request_id)
                ->update(['status' => 'in_progress']);
        }

        $req = \App\Models\ConsultingRequest::find($contract->request_id);
        if ($req) {
            NotificationService::consultingReportReviewed($req->user_id, $req->id, $req->request_code, $validated['action']);
            // إشعار المكتب أيضاً
            $officeUser = \App\Models\ConsultingOffice::find($contract->office_id)?->user_id;
            if ($officeUser) NotificationService::consultingReportReviewed($officeUser, $req->id, $req->request_code, $validated['action']);
        }

        return response()->json(['message' => 'تم تحديث حالة التقرير.', 'data' => $report]);
    }

    /* ── POST /consulting/contracts/{id}/review ── (صاحب المشروع يقيّم) ── */
    public function submitReview(Request $request, int $id): JsonResponse
    {
        $contract = ConsultingContract::findOrFail($id);
        $user     = $request->user();

        if ($contract->client_user_id !== $user->id) abort(403);

        if ($contract->request->status !== 'completed') {
            return response()->json(['message' => 'يمكن التقييم فقط بعد اكتمال الاستشارة.'], 422);
        }

        if (\App\Models\ConsultingReview::where('contract_id', $id)->where('reviewer_id', $user->id)->exists()) {
            return response()->json(['message' => 'قيّمت هذه الاستشارة مسبقاً.'], 422);
        }

        $validated = $request->validate([
            'overall_rating'       => ['required', 'integer', 'min:1', 'max:5'],
            'quality_rating'       => ['nullable', 'integer', 'min:1', 'max:5'],
            'time_rating'          => ['nullable', 'integer', 'min:1', 'max:5'],
            'communication_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comment'              => ['nullable', 'string'],
        ]);

        $review = \App\Models\ConsultingReview::create(array_merge($validated, [
            'contract_id' => $id,
            'office_id'   => $contract->office_id,
            'reviewer_id' => $user->id,
        ]));

        return response()->json(['message' => 'شكراً على تقييمك.', 'data' => $review], 201);
    }
}
