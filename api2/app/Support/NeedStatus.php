<?php

namespace App\Support;

class NeedStatus
{
    public const NEW = 'new';
    public const PENDING_GOVERNORATE_REVIEW = 'pending_governorate_review';
    public const RETURNED_FOR_EDIT = 'returned_for_edit';
    public const PENDING_BRANCH_APPROVAL = 'pending_branch_approval';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const CLASSIFIED = 'classified';
    public const IN_PROGRESS = 'in_progress';
    public const RESOLVED = 'resolved';
    public const ARCHIVED = 'archived';

    /** @var array<string, string> */
    public const LEGACY_MAP = [
        'مسودة' => self::NEW,
        'بانتظار تدقيق بيانات المحافظة' => self::PENDING_GOVERNORATE_REVIEW,
        'معاد للتعديل' => self::RETURNED_FOR_EDIT,
        'بانتظار موافقة مدير الفرع' => self::PENDING_BRANCH_APPROVAL,
        'موافق عليه' => self::APPROVED,
        'مرفوض' => self::REJECTED,
        'مصنف' => self::CLASSIFIED,
        'قيد المعالجة' => self::IN_PROGRESS,
        'تم الحل' => self::RESOLVED,
        'مؤرشف' => self::ARCHIVED,
    ];

    public static function fromLegacy(?string $legacy): string
    {
        $legacy = trim((string) $legacy);

        return self::LEGACY_MAP[$legacy] ?? self::PENDING_GOVERNORATE_REVIEW;
    }

    public static function label(string $status): string
    {
        return match ($status) {
            self::NEW => 'مسودة',
            self::PENDING_GOVERNORATE_REVIEW => 'بانتظار تدقيق بيانات المحافظة',
            self::RETURNED_FOR_EDIT => 'معاد للتعديل',
            self::PENDING_BRANCH_APPROVAL => 'بانتظار موافقة مدير الفرع',
            self::APPROVED => 'موافق عليه',
            self::REJECTED => 'مرفوض',
            self::CLASSIFIED => 'مصنف',
            self::IN_PROGRESS => 'قيد المعالجة',
            self::RESOLVED => 'تم الحل',
            self::ARCHIVED => 'مؤرشف',
            default => $status,
        };
    }

    public static function isEditable(string $status): bool
    {
        // قابل للتعديل ما دام لم يُدقَّق بعد: مسودة، أو بانتظار تدقيق المحافظة، أو مُعاد للتعديل.
        // بمجرد التدقيق (ينتقل إلى «بانتظار موافقة مدير الفرع») يُقفل التعديل.
        return in_array($status, [
            self::NEW,
            self::PENDING_GOVERNORATE_REVIEW,
            self::RETURNED_FOR_EDIT,
        ], true);
    }
}
