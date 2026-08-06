<?php

namespace App\Services\Entrepreneur;

use App\Models\EntrepreneurProfile;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EntrepreneurProfileExportService
{
    /** @var array<string, string> */
    private const FIELD_LABELS = [
        'ai' => 'الذكاء الاصطناعي',
        'mobile_apps' => 'التطبيقات الذكية',
        'software' => 'البرمجيات',
        'games' => 'الألعاب',
        'ecommerce' => 'التجارة الإلكترونية',
        'cybersecurity' => 'الأمن السيبراني',
        'fintech' => 'FinTech',
        'elearning' => 'التعليم الإلكتروني',
        'digital_health' => 'الصحة الرقمية',
        'iot' => 'IoT',
        'blockchain' => 'Blockchain',
        'other' => 'أخرى',
    ];

    /** @var array<string, string> */
    private const READINESS_LABELS = [
        'idea' => 'فكرة فقط',
        'mvp' => 'نموذج أولي',
        'usable' => 'منتج قابل للاستخدام',
        'has_users' => 'لديه مستخدمون',
        'revenue' => 'يحقق إيرادات',
        'scalable' => 'قابل للتوسع',
    ];

    /** @var array<string, string> */
    private const STATUS_LABELS = [
        'draft' => 'مسودة',
        'submitted' => 'مُقدَّمة',
        'under_review' => 'قيد المراجعة',
        'approved' => 'مقبولة',
        'rejected' => 'مرفوضة',
    ];

    /**
     * @param  array<string, mixed>  $filters
     */
    public function buildQuery(array $filters): Builder
    {
        return EntrepreneurProfile::query()
            ->when(! empty($filters['status']), fn (Builder $q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['project_field']), fn (Builder $q) => $q->where('project_field', $filters['project_field']))
            ->when(! empty($filters['governorate']), fn (Builder $q) => $q->where('governorate', $filters['governorate']))
            ->when(! empty($filters['search']), function (Builder $q) use ($filters) {
                $term = '%'.trim((string) $filters['search']).'%';
                $q->where(function (Builder $inner) use ($term) {
                    $inner->where('full_name', 'like', $term)
                        ->orWhere('project_name', 'like', $term);
                });
            });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function exportCsv(array $filters = []): StreamedResponse
    {
        $query = $this->buildQuery($filters);
        $filename = 'entrepreneur-profiles-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['#', 'الاسم', 'المشروع', 'المجال', 'المحافظة', 'الجاهزية', 'يبحث عن استثمار', 'الحالة']);

            $query->orderByDesc('id')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $profile) {
                    fputcsv($out, [
                        $profile->id,
                        $profile->full_name,
                        $profile->project_name,
                        self::FIELD_LABELS[$profile->project_field] ?? $profile->project_field,
                        $profile->governorate,
                        self::READINESS_LABELS[$profile->readiness_stage] ?? $profile->readiness_stage,
                        $profile->seeking_investment ? 'نعم' : 'لا',
                        self::STATUS_LABELS[$profile->status] ?? $profile->status,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
