<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AuditLogResource;
use App\Models\User;
use App\Services\Admin\AuditLogQueryService;
use App\Services\AuditLogService;
use App\Support\AccessControlGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogController extends Controller
{
    public function __construct(
        private AuditLogQueryService $auditLogQuery,
        private AuditLogService $auditLog,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAuditView($request);

        $perPage = max(1, min((int) $request->integer('per_page', 25), 100));
        $logs = $this->auditLogQuery->paginate($request->only([
            'search', 'user_id', 'action', 'module', 'entity_type', 'email', 'ip', 'date_from', 'date_to',
        ]), $perPage);

        return AuditLogResource::collection($logs)->response();
    }

    public function export(Request $request): StreamedResponse|JsonResponse
    {
        $this->authorizeAuditView($request);

        if (strtolower((string) $request->query('format', '')) !== 'csv') {
            return response()->json(['message' => 'صيغة غير مدعومة. استخدم format=csv'], 422);
        }

        $filters = $request->only([
            'search', 'user_id', 'action', 'module', 'entity_type', 'email', 'ip', 'date_from', 'date_to',
        ]);
        $rows = $this->auditLogQuery->export($filters);

        $this->auditLog->log(
            'activity_logs_exported',
            $request->user(),
            null,
            null,
            ['filters' => $filters, 'rows' => $rows->count()],
            null,
            $request,
            'audit',
            'تصدير سجل النشاط CSV'
        );

        $filename = 'activity-logs-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, [
                'التاريخ', 'المستخدم', 'البريد', 'العملية', 'القسم',
                'نوع الكيان', 'رقم الكيان', 'الوصف', 'IP',
            ]);
            foreach ($rows as $log) {
                fputcsv($handle, [
                    $log->created_at?->format('Y-m-d H:i:s'),
                    $log->user?->name ?? '',
                    $log->user?->email ?? '',
                    $log->action,
                    $log->module ?? '',
                    $log->auditable_type ? class_basename($log->auditable_type) : '',
                    $log->auditable_id ?? '',
                    $log->description ?? '',
                    $log->ip_address ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->authorizeAuditView($request);

        return response()->json([
            'data' => new AuditLogResource($this->auditLogQuery->findOrFail($id)),
        ]);
    }

    public function forUser(Request $request, int $id): JsonResponse
    {
        $this->authorizeAuditView($request);

        $user = User::query()->findOrFail($id);
        $perPage = max(1, min((int) $request->integer('per_page', 25), 100));
        $logs = $this->auditLogQuery->forUser($user, $request->only([
            'search', 'action', 'module', 'date_from', 'date_to',
        ]), $perPage);

        return AuditLogResource::collection($logs)->response();
    }

    private function authorizeAuditView(Request $request): void
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'غير مصرح.');
        }

        if ($user->hasPermissionTo('view_audit')) {
            return;
        }

        if (AccessControlGuard::isAccessAdministrator($user) && $user->hasPermissionTo('manage_user_access')) {
            return;
        }

        abort(403, 'غير مصرح.');
    }
}
