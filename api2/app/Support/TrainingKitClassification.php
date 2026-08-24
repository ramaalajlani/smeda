<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class TrainingKitClassification
{
    /** @var list<string> */
    public const LABELS = [
        'تعليمي',
        'مهني',
        'تدريبي',
        'مهارات',
        'تخصصي',
        'محاسبي ومالي',
        'معلوماتية وتقنية',
    ];

    /** @return list<string> */
    public static function labels(): array
    {
        return self::LABELS;
    }

    /** @return array<int, mixed> */
    public static function validationRule(): array
    {
        return ['nullable', 'string', 'max:100', Rule::in(self::LABELS)];
    }
}
