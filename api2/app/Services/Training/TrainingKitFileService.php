<?php

namespace App\Services\Training;

use App\Models\TrainingKit;
use App\Support\SecureFileStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TrainingKitFileService
{
    /** @var list<string> */
    private const PROMOTIONAL_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    /** @var list<string> */
    private const BAG_MIMES = ['application/pdf'];

    private const MAX_PROMOTIONAL_BYTES = 15 * 1024 * 1024;

    private const MAX_BAG_BYTES = 25 * 1024 * 1024;

    /**
     * @return array{path: string, original_name: string, mime: string, size: int}
     */
    public function storePromotionalFile(UploadedFile $file, TrainingKit $kit): array
    {
        $this->assertSize($file, self::MAX_PROMOTIONAL_BYTES, 'promotional_file');

        $path = SecureFileStorage::storeUploadedFile(
            $file,
            'training-kits/' . $kit->id . '/promotional',
            'public',
            self::PROMOTIONAL_MIMES
        );

        return $this->metaFromUpload($file, $path);
    }

    /**
     * Training bag file — PDF only, private disk (never public URL).
     *
     * @return array{path: string, original_name: string, mime: string, size: int}
     */
    public function storeTrainingBagFile(UploadedFile $file, TrainingKit $kit): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: '');
        if ($extension !== 'pdf') {
            throw ValidationException::withMessages([
                'training_bag_file' => ['ملف الحقيبة التدريبية يجب أن يكون بصيغة PDF.'],
            ]);
        }

        $this->assertSize($file, self::MAX_BAG_BYTES, 'training_bag_file');

        $path = SecureFileStorage::storeUploadedFile(
            $file,
            'training-kits/' . $kit->id . '/bag',
            'local',
            self::BAG_MIMES
        );

        return $this->metaFromUpload($file, $path);
    }

    public function deletePromotionalFile(TrainingKit $kit): void
    {
        $this->deleteStoredPath($kit->promotional_file_path, 'public');
    }

    public function deleteTrainingBagFile(TrainingKit $kit): void
    {
        $this->deleteStoredPath($kit->training_bag_file_path, 'local');
    }

    public function promotionalDisk(): string
    {
        return 'public';
    }

    public function bagDisk(): string
    {
        return 'local';
    }

    private function assertSize(UploadedFile $file, int $maxBytes, string $field): void
    {
        if ($file->getSize() > $maxBytes) {
            throw ValidationException::withMessages([
                $field => ['حجم الملف يتجاوز الحد المسموح (' . (int) ($maxBytes / 1024 / 1024) . ' MB).'],
            ]);
        }
    }

    /**
     * @return array{path: string, original_name: string, mime: string, size: int}
     */
    private function metaFromUpload(UploadedFile $file, string $path): array
    {
        return [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => (string) $file->getMimeType(),
            'size' => (int) $file->getSize(),
        ];
    }

    private function deleteStoredPath(?string $path, string $disk): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}
