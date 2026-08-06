<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SecureFileStorage
{
    /** @var list<string> */
    private const BLOCKED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
        'js', 'html', 'htm', 'svg', 'exe', 'bat', 'cmd', 'sh', 'bash',
    ];

    public static function storeUploadedFile(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        ?array $allowedMimes = null
    ): string {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');

        if ($extension === '' || in_array($extension, self::BLOCKED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'file' => ['نوع الملف غير مسموح.'],
            ]);
        }

        if ($allowedMimes !== null) {
            $mime = strtolower((string) $file->getMimeType());
            $allowed = array_map('strtolower', $allowedMimes);

            if (!in_array($mime, $allowed, true)) {
                throw ValidationException::withMessages([
                    'file' => ['نوع الملف غير مدعوم.'],
                ]);
            }
        }

        $filename = Str::uuid()->toString() . '.' . $extension;

        return $file->storeAs($directory, $filename, $disk);
    }
}
