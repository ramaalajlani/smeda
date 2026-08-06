<?php

namespace App\Services\Signing;

use App\Models\User;
use App\Models\UserElectronicSignature;
use App\Services\AuditLogService;
use App\Support\SecureFileStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserElectronicSignatureService
{
    /** @var list<string> */
    public const ALLOWED_MIMES = [
        'image/png',
        'image/jpeg',
        'image/jpg',
        'image/webp',
    ];

    public const MAX_BYTES = 2_097_152;

    /** @var list<string> */
    public const APPROVER_PERMISSIONS = [
        'approve_center_certificates',
        'approve_training_certificates',
        'approve_deputy_certificates',
        'approve_general_director_certificates',
    ];

    public function __construct(private AuditLogService $auditLog) {}

    public function canManageOwnSignature(User $user): bool
    {
        if ($user->hasRole(['admin', 'super_admin'])) {
            return true;
        }

        foreach (self::APPROVER_PERMISSIONS as $permission) {
            if ($user->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    public function activeForUser(User $user): ?UserElectronicSignature
    {
        return UserElectronicSignature::activeForUser((int) $user->id);
    }

    public function assertUserHasActiveSignature(User $user): UserElectronicSignature
    {
        $signature = $this->activeForUser($user);

        if (!$signature) {
            throw ValidationException::withMessages([
                'signature' => ['لا يمكن تنفيذ الاعتماد قبل إضافة التوقيع الإلكتروني إلى حسابك.'],
            ]);
        }

        if (!Storage::disk('local')->exists($signature->signature_path)) {
            throw ValidationException::withMessages([
                'signature' => ['ملف التوقيع الإلكتروني غير موجود. يرجى رفع توقيع جديد.'],
            ]);
        }

        return $signature;
    }

    public function upload(User $user, UploadedFile $file, ?User $uploadedBy = null): UserElectronicSignature
    {
        if (!$this->canManageOwnSignature($user)) {
            throw ValidationException::withMessages([
                'signature' => ['غير مخول برفع توقيع اعتماد.'],
            ]);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'signature' => ['حجم ملف التوقيع يجب ألا يتجاوز 2MB.'],
            ]);
        }

        $path = SecureFileStorage::storeUploadedFile(
            $file,
            'user-signatures/' . $user->id,
            'local',
            self::ALLOWED_MIMES
        );

        $fullPath = Storage::disk('local')->path($path);
        $hash = hash_file('sha256', $fullPath) ?: hash('sha256', (string) file_get_contents($fullPath));

        return DB::transaction(function () use ($user, $file, $path, $hash, $uploadedBy) {
            UserElectronicSignature::query()
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $record = UserElectronicSignature::query()->create([
                'user_id' => $user->id,
                'signature_path' => $path,
                'original_name' => $file->getClientOriginalName() ?: 'signature.png',
                'mime_type' => (string) $file->getMimeType(),
                'file_size' => (int) $file->getSize(),
                'file_hash' => $hash,
                'is_active' => true,
                'uploaded_by' => ($uploadedBy ?? $user)->id,
            ]);

            $this->auditLog->log('user_signature_uploaded', $uploadedBy ?? $user, $record, null, [
                'user_id' => $user->id,
                'file_hash' => $hash,
                'original_name' => $record->original_name,
            ], null, null, 'signing', 'رفع توقيع إلكتروني');

            return $record;
        });
    }

    public function deactivate(User $user): void
    {
        if (!$this->canManageOwnSignature($user)) {
            throw ValidationException::withMessages([
                'signature' => ['غير مخول بتعطيل التوقيع.'],
            ]);
        }

        $active = $this->activeForUser($user);
        if (!$active) {
            throw ValidationException::withMessages([
                'signature' => ['لا يوجد توقيع فعال لتعطيله.'],
            ]);
        }

        $active->update(['is_active' => false]);

        $this->auditLog->log('user_signature_deactivated', $user, $active, ['is_active' => true], [
            'is_active' => false,
        ], null, null, 'signing', 'تعطيل التوقيع الإلكتروني');
    }

    public function snapshotForDocument(UserElectronicSignature $signature, int $documentSignatureId): array
    {
        if (!Storage::disk('local')->exists($signature->signature_path)) {
            throw ValidationException::withMessages([
                'signature' => ['ملف التوقيع غير متاح.'],
            ]);
        }

        $extension = pathinfo($signature->signature_path, PATHINFO_EXTENSION) ?: 'png';
        $snapshotPath = 'signature-snapshots/' . $documentSignatureId . '/' . Str::uuid() . '.' . $extension;

        Storage::disk('local')->makeDirectory('signature-snapshots/' . $documentSignatureId);
        Storage::disk('local')->copy($signature->signature_path, $snapshotPath);

        $fullPath = Storage::disk('local')->path($snapshotPath);
        $hash = hash_file('sha256', $fullPath) ?: $signature->file_hash;

        return [
            'path' => $snapshotPath,
            'hash' => $hash,
        ];
    }

    public function assertSignatureBelongsToUser(UserElectronicSignature $signature, User $user): void
    {
        if ((int) $signature->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'signature' => ['لا يمكن استخدام توقيع مستخدم آخر.'],
            ]);
        }
    }
}
