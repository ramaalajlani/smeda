<?php

namespace App\Services\Signing;

use App\Models\Certificate;
use App\Models\CertificateApproval;
use App\Models\DocumentElectronicSignature;
use App\Models\ExecutiveSignerProfile;
use App\Models\User;
use App\Models\UserElectronicSignature;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ElectronicSignatureService
{
    public const ROLE_GENERAL = ExecutiveSignerProfile::ROLE_GENERAL_DIRECTOR;

    public const ROLE_DEPUTY = ExecutiveSignerProfile::ROLE_DEPUTY_GENERAL_DIRECTOR;

    /** @var array<string, string> */
    public const APPROVAL_STEP_ROLE_MAP = [
        'center_approval' => 'center_approver',
        'training_manager_approval' => 'training_manager',
        'deputy_director_approval' => self::ROLE_DEPUTY,
        'general_director_approval' => self::ROLE_GENERAL,
    ];

    /** @var list<string> */
    public const EXECUTIVE_ESIG_STEPS = [
        'deputy_director_approval',
        'general_director_approval',
    ];

    public function __construct(private UserElectronicSignatureService $userSignatures) {}

    public function signCertificateApproval(
        Certificate $certificate,
        CertificateApproval $approval,
        User $user,
        string $approvalStep
    ): DocumentElectronicSignature {
        $roleKey = $this->roleForApprovalStep($approvalStep);
        if (!$roleKey) {
            throw ValidationException::withMessages([
                'approval_step' => ['مرحلة اعتماد غير مدعومة للتوقيع.'],
            ]);
        }

        $this->assertUserCanSignStep($user, $approvalStep);

        if ($approval->electronicSignature) {
            throw ValidationException::withMessages([
                'signature' => ['تم إنشاء توقيع إلكتروني لهذه المرحلة مسبقاً.'],
            ]);
        }

        $userSignature = $this->userSignatures->assertUserHasActiveSignature($user);
        $this->userSignatures->assertSignatureBelongsToUser($userSignature, $user);

        $signerName = $user->name ?: 'غير محدد';
        $signerTitle = $this->titleForApprovalStep($approvalStep, $user);

        if (in_array($approvalStep, self::EXECUTIVE_ESIG_STEPS, true)) {
            $profile = ExecutiveSignerProfile::forRole($roleKey);
            $signerName = $profile?->signer_name ?: $signerName;
            $signerTitle = $profile?->signer_title ?: $signerTitle;
        }

        $documentHash = $this->buildCertificateApprovalHash($certificate, $approval, $userSignature);
        $usesEsig = in_array($approvalStep, self::EXECUTIVE_ESIG_STEPS, true);
        $verificationCode = $usesEsig ? $this->generateVerificationCode('ESIG') : $this->generateVerificationCode('CAPPR');

        $record = DocumentElectronicSignature::query()->create([
            'signable_type' => CertificateApproval::class,
            'signable_id' => $approval->id,
            'role_key' => $roleKey,
            'signed_by_user_id' => $user->id,
            'user_electronic_signature_id' => $userSignature->id,
            'signer_name' => $signerName,
            'signer_title' => $signerTitle,
            'document_hash' => $documentHash,
            'signature_hmac' => hash('sha256', 'pending'),
            'verification_code' => $verificationCode,
            'signed_at' => now(),
        ]);

        $snapshot = $this->userSignatures->snapshotForDocument($userSignature, (int) $record->id);
        $signatureHmac = $this->computeSignatureHmac(
            $documentHash,
            $roleKey,
            (int) $user->id,
            $verificationCode,
            $snapshot['hash']
        );

        $record->update([
            'signature_image_path' => $snapshot['path'],
            'signature_image_hash' => $snapshot['hash'],
            'signature_hmac' => $signatureHmac,
        ]);

        return $record->fresh();
    }

    public function verify(string $verificationCode): ?array
    {
        $record = DocumentElectronicSignature::query()
            ->where('verification_code', strtoupper(trim($verificationCode)))
            ->first();

        if (!$record) {
            return null;
        }

        $expected = $this->computeSignatureHmac(
            $record->document_hash,
            $record->role_key,
            (int) $record->signed_by_user_id,
            $record->verification_code,
            $record->signature_image_hash
        );

        if (!hash_equals($expected, $record->signature_hmac)) {
            return null;
        }

        $certificate = null;
        if ($record->signable_type === CertificateApproval::class) {
            $approval = CertificateApproval::query()->with('certificate')->find($record->signable_id);
            $certificate = $approval?->certificate;
        }

        return [
            'valid' => true,
            'verification_code' => $record->verification_code,
            'role_key' => $record->role_key,
            'role_label' => $this->roleLabel($record->role_key),
            'signer_name' => $record->signer_name,
            'signer_title' => $record->signer_title,
            'signed_at' => optional($record->signed_at)->toIso8601String(),
            'document_type' => $record->signable_type === CertificateApproval::class ? 'certificate_approval' : 'document',
            'certificate_code' => $certificate?->certificate_code,
            'certificate_number' => $certificate?->certificate_number,
            'has_signature_image' => (bool) $record->signature_image_path,
        ];
    }

    public function roleForApprovalStep(string $approvalStep): ?string
    {
        return self::APPROVAL_STEP_ROLE_MAP[$approvalStep] ?? null;
    }

    public function requiresPersonalSignature(string $approvalStep): bool
    {
        return isset(self::APPROVAL_STEP_ROLE_MAP[$approvalStep]);
    }

    public function buildCertificateApprovalHash(
        Certificate $certificate,
        CertificateApproval $approval,
        ?UserElectronicSignature $userSignature = null
    ): string {
        $payload = implode('|', [
            'certificate',
            (string) $certificate->id,
            (string) $certificate->certificate_code,
            (string) $certificate->certificate_number,
            (string) $certificate->verification_code,
            (string) $approval->id,
            (string) $approval->approval_step,
            (string) optional($certificate->issued_at)->timestamp,
            (string) ($userSignature?->file_hash ?? ''),
        ]);

        return hash('sha256', $payload);
    }

    private function computeSignatureHmac(
        string $documentHash,
        string $roleKey,
        int $userId,
        string $verificationCode,
        ?string $imageHash = null
    ): string {
        $message = implode('|', [$documentHash, $roleKey, (string) $userId, $verificationCode, (string) $imageHash]);

        return hash_hmac('sha256', $message, $this->signingKey());
    }

    private function signingKey(): string
    {
        return hash('sha256', (string) config('signing.executive_key') . '|executive-signing-v1');
    }

    private function generateVerificationCode(string $prefix = 'ESIG'): string
    {
        $prefix = strtoupper($prefix);

        do {
            $code = $prefix . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (DocumentElectronicSignature::query()->where('verification_code', $code)->exists());

        return $code;
    }

    private function assertUserCanSignStep(User $user, string $approvalStep): void
    {
        $allowed = match ($approvalStep) {
            'center_approval' => $user->hasRole(['center_user', 'branch_manager', 'admin', 'super_admin'])
                || $user->hasPermissionTo('approve_center_certificates'),
            'training_manager_approval' => $user->hasRole(['training_manager', 'deputy_general_director', 'admin', 'super_admin'])
                || $user->hasPermissionTo('approve_training_certificates'),
            'deputy_director_approval' => $user->hasRole(['deputy_general_director', 'deputy_director', 'admin', 'super_admin'])
                || $user->hasPermissionTo('approve_deputy_certificates'),
            'general_director_approval' => $user->hasRole(['general_director', 'admin', 'super_admin'])
                || $user->hasPermissionTo('approve_general_director_certificates'),
            default => false,
        };

        if (!$allowed) {
            throw ValidationException::withMessages([
                'signature' => ['المستخدم غير مخول بإصدار هذا التوقيع الإلكتروني.'],
            ]);
        }
    }

    private function titleForApprovalStep(string $approvalStep, User $user): string
    {
        return match ($approvalStep) {
            'center_approval' => 'اعتماد المركز',
            'training_manager_approval' => 'اعتماد قسم التدريب',
            'deputy_director_approval' => 'نائب المدير العام',
            'general_director_approval' => 'المدير العام',
            default => 'المعتمد',
        };
    }

    private function roleLabel(string $roleKey): string
    {
        return match ($roleKey) {
            'center_approver' => 'اعتماد المركز',
            'training_manager' => 'اعتماد قسم التدريب',
            self::ROLE_GENERAL => 'المدير العام',
            self::ROLE_DEPUTY => 'نائب المدير العام',
            default => 'المعتمد',
        };
    }
}
