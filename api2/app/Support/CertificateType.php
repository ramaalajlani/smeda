<?php

namespace App\Support;

class CertificateType
{
    public const ATTENDANCE = 'attendance';

    /** Stored value in DB (legacy enum). */
    public const COMPLETION = 'pass';

    /** API alias accepted from clients. */
    public const COMPLETION_ALIAS = 'completion';

    /** @var list<string> */
    public const ALL = [
        self::ATTENDANCE,
        self::COMPLETION,
    ];

    public static function normalize(?string $type): string
    {
        $value = strtolower(trim((string) $type));

        return match ($value) {
            'completion', 'pass' => self::COMPLETION,
            'attendance' => self::ATTENDANCE,
            default => $value,
        };
    }

    public static function label(?string $type): string
    {
        return match (self::normalize($type)) {
            self::ATTENDANCE => 'شهادة حضور',
            self::COMPLETION => 'شهادة اجتياز تدريب',
            default => 'شهادة تدريب',
        };
    }

    public static function isCompletion(?string $type): bool
    {
        return self::normalize($type) === self::COMPLETION;
    }

    public static function isAttendance(?string $type): bool
    {
        return self::normalize($type) === self::ATTENDANCE;
    }
}
