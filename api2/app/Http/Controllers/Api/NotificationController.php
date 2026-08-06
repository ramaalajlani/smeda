<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private NotificationSummaryService $summaryService) {}

    /* GET /notifications */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $rows = Notification::where('user_id', $user->id)
            ->when($request->filled('unread_only'), fn ($q) => $q->where('is_read', false))
            ->orderByDesc('created_at')
            ->paginate(max(1, min($request->integer('per_page', 30), 100)));

        return response()->json($rows);
    }

    /* GET /notifications/summary  — عدد غير المقروءة + آخر 5 */
    public function summary(Request $request): JsonResponse
    {
        return response()->json($this->summaryService->summary($request->user()));
    }

    /* POST /notifications/{id}/read */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $n = Notification::where('user_id', $request->user()->id)->findOrFail($id);
        $n->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['message' => 'تم التحديد كمقروء.']);
    }

    /* POST /notifications/read-all */
    public function markAllRead(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['message' => 'تم تحديد كل الإشعارات كمقروءة.']);
    }

    /* DELETE /notifications/{id} */
    public function destroy(Request $request, int $id): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)->findOrFail($id)->delete();
        return response()->json(['message' => 'تم حذف الإشعار.']);
    }
}
