<?php

namespace App\Services\Needs;

use App\Models\Need;
use App\Support\NeedDataScope;
use App\Support\NeedStatus;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NeedExportService
{
    public function __construct(private NeedDashboardService $dashboard) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function exportCsv(User $user, array $filters = []): StreamedResponse
    {
        $query = NeedDataScope::scopeNeeds(Need::query()->with(['governorate:id,name_ar', 'branch:id,name']), $user);
        $this->dashboard->applyFilters($query, $filters);

        $filename = 'needs-export-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['الرمز', 'العنوان', 'النوع', 'المحافظة', 'الفرع', 'القطاع', 'الأولوية', 'الحالة', 'المصدر', 'التدخل']);

            $query->orderByDesc('id')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $need) {
                    fputcsv($out, [
                        $need->need_code,
                        $need->title,
                        $need->need_type,
                        $need->governorate?->name_ar,
                        $need->branch?->name,
                        $need->sector,
                        $need->priority,
                        NeedStatus::label($need->status),
                        $need->source_platform,
                        $need->proposed_intervention,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
