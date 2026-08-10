<?php

namespace App\Services\Needs;

use App\Models\FundingApplication;
use App\Models\Governorate;
use App\Models\Need;
use App\Models\User;
use App\Support\NeedStatus;
use App\Support\NeedTaxonomy;

class NeedSyncService
{
    /** @var array<string, array{0: float, 1: float}> */
    private const GOVERNORATE_CENTERS = [
        'دمشق' => [33.513, 36.291],
        'حلب' => [36.202, 37.161],
        'ريف دمشق' => [33.583, 36.450],
        'حمص' => [34.732, 36.713],
        'حماة' => [35.132, 36.757],
        'اللاذقية' => [35.532, 35.791],
        'إدلب' => [35.931, 36.634],
        'الحسكة' => [36.512, 40.752],
        'دير الزور' => [35.336, 40.141],
        'طرطوس' => [34.893, 35.887],
        'الرقة' => [35.953, 39.006],
        'درعا' => [32.625, 36.103],
        'السويداء' => [32.708, 36.566],
        'القنيطرة' => [33.125, 35.825],
    ];

    /** @var array<string, string> */
    private const FINANCE_SECTOR_MAP = [
        'agricultural' => 'agriculture',
        'agriculture' => 'agriculture',
        'industrial' => 'industry',
        'industry' => 'industry',
        'commercial' => 'trade',
        'trade' => 'trade',
        'service' => 'services',
        'services' => 'services',
    ];

    public function __construct(private NeedWorkflowService $workflow) {}

    /**
     * @param  array<string, mixed>  $overrides
     */
    public function createManual(User $user, array $overrides = []): Need
    {
        return $this->workflow->create($user, array_merge([
            'title' => 'احتياج يدوي',
            'description' => 'احتياج مسجل يدوياً',
            'need_owner_type' => 'citizen',
            'need_scope' => 'individual',
            'source_platform' => 'manual',
        ], $overrides));
    }

    public function createFromFundingApplication(FundingApplication $application, User $user): Need
    {
        $existing = Need::query()
            ->where('source_platform', 'finance')
            ->where('source_module', 'funding_application')
            ->where('source_record_id', $application->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $existingByFk = Need::query()
            ->where('funding_application_id', $application->id)
            ->first();

        if ($existingByFk) {
            return $existingByFk;
        }

        $application->loadMissing('governorate');
        $point = $this->resolveMapPoint($application);
        $sectorCode = $this->mapFinanceSector($application->project_sector);

        return $this->workflow->create($user, [
            'title' => 'احتياج تمويل: ' . $application->project_name,
            'description' => $application->purpose ?? $application->description,
            'summary' => mb_substr((string) ($application->description ?? $application->project_name), 0, 180),
            'need_owner_type' => 'citizen',
            'need_scope' => 'project',
            'need_type' => 'تمويل',
            'need_category' => 'project_development',
            'targeting_type' => $application->project_status === 'existing' ? 'existing_project' : 'entrepreneurs',
            'source_platform' => 'finance',
            'source_module' => 'funding_application',
            'source_record_id' => $application->id,
            'governorate_id' => $application->governorate_id,
            'branch_id' => $application->branch_id,
            'sector' => $sectorCode ? NeedTaxonomy::sectorLabel($sectorCode) : $application->project_sector,
            'sectors' => $sectorCode ? [$sectorCode] : [],
            'priority' => 'high',
            'proposed_intervention' => 'تمويل',
            'applicant_name' => $application->applicant_name,
            'applicant_phone' => $application->phone,
            'applicant_email' => $application->email,
            'funding_application_id' => $application->id,
            'latitude' => $point['latitude'],
            'longitude' => $point['longitude'],
            'location_source' => $point['location_source'],
            'is_public' => true,
            'notes' => 'مزامن تلقائياً من طلب التمويل رقم ' . ($application->application_number ?: $application->id),
            'metadata' => [
                'funding_application_number' => $application->application_number,
                'requested_amount' => $application->requested_amount,
                'currency' => $application->currency,
                'financing_mode' => $application->financing_mode,
                'project_status' => $application->project_status,
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromTrainingRequest(User $user, array $data): Need
    {
        $sourceId = (int) ($data['source_record_id'] ?? 0);

        if ($sourceId && Need::query()->where('source_platform', 'training')->where('source_record_id', $sourceId)->exists()) {
            return Need::query()->where('source_platform', 'training')->where('source_record_id', $sourceId)->firstOrFail();
        }

        return $this->workflow->create($user, [
            'title' => $data['title'] ?? 'احتياج تدريب',
            'description' => $data['description'] ?? null,
            'need_owner_type' => $data['need_owner_type'] ?? 'citizen',
            'need_scope' => $data['need_scope'] ?? 'individual',
            'need_type' => $data['need_type'] ?? 'تدريب',
            'source_platform' => 'training',
            'source_module' => $data['source_module'] ?? 'training_request',
            'source_record_id' => $sourceId ?: null,
            'governorate_id' => $data['governorate_id'] ?? $user->governorate_id,
            'branch_id' => $data['branch_id'] ?? $user->branch_id,
            'sector' => $data['sector'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'proposed_intervention' => 'تدريب',
            'training_course_id' => $data['training_course_id'] ?? null,
        ]);
    }

    public function createFromConsultantReport(array $data, User $user): Need
    {
        return $this->workflow->create($user, array_merge($data, [
            'source_platform' => 'finance',
            'proposed_intervention' => $data['proposed_intervention'] ?? 'استشارات',
        ]));
    }

    public function createFromBankRejection(FundingApplication $application, User $user, string $reason): Need
    {
        $point = $this->resolveMapPoint($application);

        return $this->workflow->create($user, [
            'title' => 'احتياج بعد رفض تمويل: ' . $application->project_name,
            'description' => $reason,
            'need_owner_type' => 'citizen',
            'need_scope' => 'project',
            'need_type' => 'تدريب',
            'source_platform' => 'finance',
            'source_module' => 'bank_rejection',
            'source_record_id' => $application->id,
            'governorate_id' => $application->governorate_id,
            'branch_id' => $application->branch_id,
            'priority' => 'high',
            'proposed_intervention' => 'تدريب',
            'funding_application_id' => $application->id,
            'latitude' => $point['latitude'],
            'longitude' => $point['longitude'],
            'location_source' => $point['location_source'],
            'status' => NeedStatus::PENDING_GOVERNORATE_REVIEW,
        ]);
    }

    private function mapFinanceSector(?string $sector): ?string
    {
        if (!$sector) {
            return null;
        }

        return self::FINANCE_SECTOR_MAP[strtolower(trim($sector))] ?? null;
    }

    /**
     * @return array{latitude: ?float, longitude: ?float, location_source: ?string}
     */
    private function resolveMapPoint(FundingApplication $application): array
    {
        $governorate = $application->relationLoaded('governorate')
            ? $application->governorate
            : Governorate::query()->find($application->governorate_id);

        $name = trim((string) ($governorate?->name_ar ?? ''));
        $center = self::GOVERNORATE_CENTERS[$name] ?? null;

        if (!$center && $name !== '') {
            foreach (self::GOVERNORATE_CENTERS as $label => $coords) {
                if (str_contains($name, $label) || str_contains($label, $name)) {
                    $center = $coords;
                    break;
                }
            }
        }

        if (!$center) {
            return [
                'latitude' => null,
                'longitude' => null,
                'location_source' => null,
            ];
        }

        $id = (int) $application->id;
        $latJitter = (($id % 17) - 8) * 0.008;
        $lngJitter = (($id % 13) - 6) * 0.008;

        return [
            'latitude' => round($center[0] + $latJitter, 6),
            'longitude' => round($center[1] + $lngJitter, 6),
            'location_source' => 'governorate_center',
        ];
    }
}
