<?php

namespace App\Support;

use App\Models\ConsultingRequest;
use App\Models\FundingApplication;
use App\Models\Need;
use Illuminate\Validation\ValidationException;

class StatusTransitionValidator
{
    /** @var array<string, array<string, list<string>>> */
    private const TRANSITIONS = [
        Need::class => [
            NeedStatus::PENDING_GOVERNORATE_REVIEW => [
                NeedStatus::PENDING_BRANCH_APPROVAL,
                NeedStatus::RETURNED_FOR_EDIT,
                NeedStatus::REJECTED,
            ],
            NeedStatus::RETURNED_FOR_EDIT => [
                NeedStatus::PENDING_BRANCH_APPROVAL,
                NeedStatus::REJECTED,
            ],
            NeedStatus::PENDING_BRANCH_APPROVAL => [
                NeedStatus::APPROVED,
                NeedStatus::REJECTED,
                NeedStatus::RETURNED_FOR_EDIT,
            ],
            NeedStatus::APPROVED => [
                NeedStatus::CLASSIFIED,
                NeedStatus::RESOLVED,
            ],
            NeedStatus::CLASSIFIED => [
                NeedStatus::CLASSIFIED,
                NeedStatus::RESOLVED,
            ],
            NeedStatus::REJECTED => [],
            NeedStatus::RESOLVED => [],
        ],
        FundingApplication::class => [
            'draft' => ['submitted'],
            'needs_completion' => ['submitted', 'branch_review', 'funder_review', 'consultant_review', 'rejected'],
            'submitted' => ['branch_review', 'funder_review', 'consultant_review', 'needs_completion', 'rejected'],
            'branch_review' => ['branch_review', 'funder_review', 'consultant_review', 'needs_completion', 'rejected'],
            'consultant_review' => ['consultant_review', 'funder_review', 'approved', 'rejected'],
            'consultant_priced' => ['funder_review', 'approved', 'rejected'],
            'funder_review' => ['funder_review', 'approved', 'rejected'],
            'approved' => ['funded', 'rejected'],
            'rejected' => [],
            'funded' => [],
        ],
        ConsultingRequest::class => [
            'draft' => ['submitted'],
            'needs_info' => ['submitted', 'transferred_financing', 'transferred_training', 'transferred_incubation', 'transferred_gis'],
            'submitted' => ['awaiting_offers', 'needs_info', 'transferred_financing', 'transferred_training', 'transferred_incubation', 'transferred_gis'],
            'awaiting_offers' => ['offer_submitted', 'needs_info', 'transferred_financing', 'transferred_training', 'transferred_incubation', 'transferred_gis'],
            'offer_submitted' => ['in_progress', 'needs_info', 'transferred_financing', 'transferred_training', 'transferred_incubation', 'transferred_gis'],
            'in_progress' => ['transferred_financing', 'transferred_training', 'transferred_incubation', 'transferred_gis'],
        ],
    ];

    public function assertAllowed(string $modelClass, ?string $fromStatus, string $toStatus): void
    {
        if ($fromStatus === null || $fromStatus === '') {
            return;
        }

        $rules = self::TRANSITIONS[$modelClass] ?? null;
        if ($rules === null) {
            return;
        }

        $allowed = $rules[$fromStatus] ?? null;
        if ($allowed === null) {
            return;
        }

        if (!in_array($toStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => ['انتقال الحالة غير مسموح من ' . $fromStatus . ' إلى ' . $toStatus . '.'],
            ]);
        }
    }
}
