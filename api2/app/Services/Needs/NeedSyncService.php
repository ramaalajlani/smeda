<?php

namespace App\Services\Needs;

use App\Models\FundingApplication;
use App\Models\Need;
use App\Models\User;
use App\Support\NeedStatus;

class NeedSyncService
{
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
        if (Need::query()->where('source_platform', 'finance')->where('source_record_id', $application->id)->exists()) {
            return Need::query()->where('source_platform', 'finance')->where('source_record_id', $application->id)->firstOrFail();
        }

        return $this->workflow->create($user, [
            'title' => 'احتياج تمويل: ' . $application->project_name,
            'description' => $application->purpose ?? $application->description,
            'summary' => mb_substr((string) ($application->description ?? $application->project_name), 0, 180),
            'need_owner_type' => 'citizen',
            'need_scope' => 'project',
            'need_type' => 'تمويل',
            'source_platform' => 'finance',
            'source_module' => 'funding_application',
            'source_record_id' => $application->id,
            'governorate_id' => $application->governorate_id,
            'branch_id' => $application->branch_id,
            'sector' => $application->project_sector,
            'priority' => 'high',
            'proposed_intervention' => 'تمويل',
            'applicant_name' => $application->applicant_name,
            'applicant_phone' => $application->phone,
            'applicant_email' => $application->email,
            'funding_application_id' => $application->id,
            'latitude' => null,
            'longitude' => null,
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
            'status' => NeedStatus::PENDING_GOVERNORATE_REVIEW,
        ]);
    }
}
