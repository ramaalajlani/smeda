<?php

namespace App\Services\OldDataImport;

class PhoneSanitizer
{
    private const MIN_DIGITS = 7;

    private const MAX_DIGITS = 20;

    private const MAX_STORED_LENGTH = 30;

    public function sanitize(?string $raw, ?ImportReport $report = null, array $context = []): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $original = trim($raw);
        $original = $this->stripInvisibleCharacters($original);
        $original = $this->normalizeDigitCharacters($original);

        if ($this->isProseNotPhone($original)) {
            $this->recordRejected($report, $original, $context);

            return null;
        }

        $hasPlusPrefix = str_starts_with($original, '+');
        $normalized = preg_replace('/[^\d+]/', '', $original) ?? '';
        $digitsOnly = preg_replace('/\D/', '', $normalized) ?? '';
        $digitCount = strlen($digitsOnly);

        if ($digitCount < self::MIN_DIGITS || $digitCount > self::MAX_DIGITS) {
            $this->recordRejected($report, $original, $context);

            return null;
        }

        $stored = $hasPlusPrefix ? '+'.$digitsOnly : $digitsOnly;

        if (strlen($stored) > self::MAX_STORED_LENGTH) {
            $this->recordRejected($report, $original, $context);

            return null;
        }

        return $stored;
    }

    private function isProseNotPhone(string $value): bool
    {
        if (preg_match('/\p{Arabic}{2,}/u', $value)) {
            return true;
        }

        $digitCount = preg_match_all('/\d/', $value);

        if (preg_match('/[a-zA-Z]{4,}/', $value) && $digitCount < self::MIN_DIGITS) {
            return true;
        }

        if (substr_count($value, ' ') >= 2 && $digitCount < self::MIN_DIGITS) {
            return true;
        }

        return false;
    }

    private function normalizeDigitCharacters(string $value): string
    {
        $from = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $to = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($from, $to, $value);
    }

    private function stripInvisibleCharacters(string $value): string
    {
        return preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{2069}\x{FEFF}]/u', '', $value) ?? $value;
    }

    private function recordRejected(?ImportReport $report, string $original, array $context): void
    {
        if ($report === null) {
            return;
        }

        $report->recordInvalidPhone($original, $context);
    }
}
