<?php

namespace App\Services\OldDataImport;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ValueResolver
{
    /** @var array<string, int> */
    private array $governorateByCode = [];

    /** @var array<string, int> */
    private array $branchByCode = [];

    public function __construct(
        private readonly ?ImportReport $report = null,
        private readonly ?PhoneSanitizer $phoneSanitizer = null,
    ) {
        if (Schema::hasTable('governorates')) {
            $this->governorateByCode = DB::table('governorates')->pluck('id', 'code')->all();
        }

        if (Schema::hasTable('branches')) {
            $this->branchByCode = DB::table('branches')->pluck('id', 'code')->all();
        }
    }

    public function governorateIdFromArabicName(?string $name): ?int
    {
        if (blank($name)) {
            return $this->fallbackGovernorateId();
        }

        $normalized = trim($name);
        $map = config('old_data_import.governorate_name_map', []);
        $code = $map[$normalized] ?? null;

        if ($code && isset($this->governorateByCode[$code])) {
            return (int) $this->governorateByCode[$code];
        }

        $id = DB::table('governorates')
            ->where('name_ar', $normalized)
            ->orWhere('name_en', 'like', '%'.$normalized.'%')
            ->value('id');

        return $id ? (int) $id : $this->fallbackGovernorateId();
    }

    public function branchIdFromGovernorateName(?string $name): ?int
    {
        $governorateId = $this->governorateIdFromArabicName($name);
        if (! $governorateId) {
            return $this->fallbackBranchId();
        }

        $branchId = DB::table('branches')
            ->where('governorate_id', $governorateId)
            ->orderBy('id')
            ->value('id');

        return $branchId ? (int) $branchId : $this->fallbackBranchId();
    }

    public function fallbackGovernorateId(): ?int
    {
        $code = config('old_data_import.defaults.fallback_governorate_code');

        return isset($this->governorateByCode[$code]) ? (int) $this->governorateByCode[$code] : null;
    }

    public function fallbackBranchId(): ?int
    {
        $code = config('old_data_import.defaults.fallback_branch_code');

        return isset($this->branchByCode[$code]) ? (int) $this->branchByCode[$code] : null;
    }

    public function mapProjectStage(?string $stage): string
    {
        $stage = trim((string) $stage);

        return match (true) {
            str_contains($stage, 'فكرة'), str_contains(strtolower($stage), 'idea') => 'idea',
            str_contains($stage, 'Prototype'), str_contains($stage, 'نموذج') => 'startup',
            str_contains($stage, 'قيد التشغيل'), str_contains($stage, 'قائم') => 'existing',
            default => 'startup',
        };
    }

    public function mapProjectSize(?string $type): string
    {
        return match (trim((string) $type)) {
            'متناهي الصغر', 'صغير', 'منزلي' => 'micro',
            'متوسط' => 'small',
            default => 'small',
        };
    }

    public function generateApplicationNumber(int $surveyId): string
    {
        return 'FA-LEG-'.str_pad((string) $surveyId, 6, '0', STR_PAD_LEFT);
    }

    public function generateNeedCode(int $surveyId): string
    {
        return 'NEED-LEG-'.str_pad((string) $surveyId, 6, '0', STR_PAD_LEFT);
    }

    public function passwordNote(?string $hash): string
    {
        if (blank($hash)) {
            return 'Missing password hash — user must reset password.';
        }

        if (Str::startsWith($hash, ['$2y$', '$2a$', '$2b$'])) {
            return config('old_data_import.defaults.imported_user_password_note');
        }

        return config('old_data_import.defaults.incompatible_password_note');
    }

    public function sanitizePhone(?string $raw, array $context = []): ?string
    {
        $sanitizer = $this->phoneSanitizer ?? new PhoneSanitizer;

        return $sanitizer->sanitize($raw, $this->report, $context);
    }

    /**
     * @param  array<string, mixed>  $survey
     * @return array<string, mixed>
     */
    public function surveyToFundingApplication(array $survey, int $applicantUserId, int $createdBy): array
    {
        $data = json_decode($survey['data_json'] ?? '{}', true) ?: [];
        $finance = $data['finance'] ?? $data['funding'] ?? [];
        $amount = (float) ($finance['requested_amount'] ?? $finance['amount'] ?? 0);

        return [
            'application_number' => $this->generateApplicationNumber((int) $survey['id']),
            'applicant_user_id' => $applicantUserId,
            'applicant_name' => $survey['full_name'] ?? 'Unknown',
            'phone' => $this->sanitizePhone($survey['phone'] ?? null, [
                'source' => 'entrep.entrepreneur_surveys',
                'old_id' => $survey['id'] ?? null,
                'column' => 'phone',
            ]),
            'email' => $survey['email'] ?? null,
            'governorate_id' => $this->governorateIdFromArabicName($survey['governorate'] ?? null),
            'branch_id' => $this->branchIdFromGovernorateName($survey['governorate'] ?? null),
            'project_name' => $survey['project_name'] ?? ('Project #'.$survey['id']),
            'project_type' => $survey['project_type'] ?? null,
            'project_sector' => $survey['sector'] ?? null,
            'project_size' => $this->mapProjectSize($survey['project_type'] ?? null),
            'business_stage' => $this->mapProjectStage($survey['project_stage'] ?? null),
            'requested_amount' => $amount > 0 ? $amount : 1,
            'currency' => 'SYP',
            'financing_type' => 'capital',
            'purpose' => $data['project_info']['main_activity'] ?? null,
            'description' => $data['project_info']['sub_activity'] ?? null,
            'status' => 'submitted',
            'current_stage' => 'legacy_import',
            'submitted_at' => $survey['created_at'] ?? now(),
            'created_by' => $createdBy,
            'updated_by' => $createdBy,
            'created_at' => $survey['created_at'] ?? now(),
            'updated_at' => $survey['created_at'] ?? now(),
        ];
    }

    /**
     * @param  array<string, mixed>  $survey
     * @return array<string, mixed>
     */
    public function surveyToNeed(array $survey, int $createdBy, ?int $fundingApplicationId = null, ?string $applicantPhone = null): array
    {
        return [
            'need_code' => $this->generateNeedCode((int) $survey['id']),
            'title' => $survey['project_name'] ?? ('Need #'.$survey['id']),
            'description' => 'Imported from entrepreneur survey #'.$survey['id'],
            'summary' => ($survey['sector'] ?? '').' / '.($survey['project_stage'] ?? ''),
            'need_owner_type' => 'citizen',
            'need_scope' => 'project',
            'need_type' => $survey['project_type'] ?? null,
            'need_category' => $survey['sector'] ?? null,
            'need_complexity' => 'specific',
            'source_platform' => 'finance',
            'source_module' => 'entrepreneur_surveys',
            'source_record_id' => (int) $survey['id'],
            'governorate_id' => $this->governorateIdFromArabicName($survey['governorate'] ?? null),
            'branch_id' => $this->branchIdFromGovernorateName($survey['governorate'] ?? null),
            'latitude' => $survey['project_lat'] ?? null,
            'longitude' => $survey['project_lng'] ?? null,
            'sector' => $survey['sector'] ?? null,
            'priority' => 'medium',
            'status' => 'approved',
            'applicant_name' => $survey['full_name'] ?? null,
            'applicant_phone' => $applicantPhone ?? $this->sanitizePhone($survey['phone'] ?? null, [
                'source' => 'entrep.entrepreneur_surveys→needs',
                'old_id' => $survey['id'] ?? null,
                'column' => 'applicant_phone',
            ]),
            'applicant_email' => $survey['email'] ?? null,
            'expected_jobs_count' => (int) ($survey['direct_jobs'] ?? 0),
            'beneficiaries_count' => (int) (($survey['direct_jobs'] ?? 0) + ($survey['indirect_jobs'] ?? 0)),
            'funding_application_id' => $fundingApplicationId,
            'created_by' => $createdBy,
            'approved_by' => $createdBy,
            'approved_at' => $survey['created_at'] ?? now(),
            'is_public' => false,
            'is_mapped' => ! empty($survey['project_lat']) && ! empty($survey['project_lng']),
            'created_at' => $survey['created_at'] ?? now(),
            'updated_at' => $survey['created_at'] ?? now(),
        ];
    }
}
