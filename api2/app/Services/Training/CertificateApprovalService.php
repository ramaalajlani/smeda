<?php

namespace App\Services\Training;

use App\DTOs\Training\ApproveCertificateData;
use App\Models\Certificate;
use App\Models\CertificateApproval;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Signing\ElectronicSignatureService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CertificateApprovalService
{
    public function __construct(
        private CertificateService $certificateService,
        private AuditLogService $auditLog,
        private ElectronicSignatureService $electronicSignatureService,
    ) {}

    public function approve(Certificate $certificate, ApproveCertificateData $data, User $user): Certificate
    {
        if ($data->decision === 'rejected' && blank($data->notes)) {
            throw ValidationException::withMessages(['notes' => ['يجب ذكر سبب الرفض.']]);
        }

        if (in_array($certificate->status, ['rejected', 'cancelled'], true)) {
            throw ValidationException::withMessages(['certificate' => ['لا يمكن اعتماد شهادة مرفوضة أو ملغاة.']]);
        }

        if ($certificate->status === 'approved') {
            throw ValidationException::withMessages(['certificate' => ['الشهادة معتمدة نهائياً ولا يمكن تعديل مراحل الاعتماد.']]);
        }

        if ($message = $this->ensureApprovalOrder($certificate, $data->approvalStep)) {
            throw ValidationException::withMessages(['approval_step' => [$message]]);
        }

        DB::transaction(function () use ($certificate, $data, $user) {
            $approval = CertificateApproval::query()
                ->where('certificate_id', $certificate->id)
                ->where('approval_step', $data->approvalStep)
                ->firstOrFail();

            if ($approval->decision !== 'pending') {
                throw ValidationException::withMessages([
                    'approval_step' => ['تمت معالجة هذه المرحلة مسبقاً ولا يمكن تعديلها من جديد.'],
                ]);
            }

            $approval->update([
                'approved_by' => $user->id,
                'decision' => $data->decision,
                'decision_at' => now(),
                'notes' => $data->notes,
            ]);

            if ($data->decision === 'approved') {
                if ($this->electronicSignatureService->requiresPersonalSignature($data->approvalStep)) {
                    $this->electronicSignatureService->signCertificateApproval(
                        $certificate->fresh(),
                        $approval->fresh(),
                        $user,
                        $data->approvalStep
                    );
                }
            }

            $approvals = CertificateApproval::query()
                ->where('certificate_id', $certificate->id)
                ->get();

            if ($approvals->contains(fn ($item) => $item->decision === 'rejected')) {
                $certificate->update([
                    'status' => 'rejected',
                    'is_verified' => false,
                    'verified_at' => null,
                    'qr_code_path' => null,
                ]);

                $this->auditLog->log('certificate_rejected', $user, $certificate, null, [
                    'approval_step' => $data->approvalStep,
                ]);

                return;
            }

            $nextStatus = $this->determineNextStatus($approvals);
            $isFullyApproved = $nextStatus === 'approved';

            $certificate->update([
                'status' => $nextStatus,
                'is_verified' => $isFullyApproved,
                'verified_at' => $isFullyApproved ? now() : null,
                'qr_code_path' => $isFullyApproved ? $certificate->qr_code_path : null,
            ]);

            if ($isFullyApproved) {
                $this->certificateService->generateQrForCertificate($certificate->fresh());
            }

            $this->auditLog->log('certificate_approved', $user, $certificate, null, [
                'approval_step' => $data->approvalStep,
                'status' => $nextStatus,
            ]);
        });

        return $certificate->refresh()->load($this->certificateService->defaultRelations());
    }

    public function ensureApprovalOrder(Certificate $certificate, string $approvalStep): ?string
    {
        $requiredStepByStatus = [
            'pending_center_approval' => 'center_approval',
            'pending_training_approval' => 'training_manager_approval',
            'pending_deputy_approval' => 'deputy_director_approval',
            'pending_general_director_approval' => 'general_director_approval',
        ];

        if (
            isset($requiredStepByStatus[$certificate->status]) &&
            $requiredStepByStatus[$certificate->status] !== $approvalStep
        ) {
            return 'لا يمكن تنفيذ هذه المرحلة قبل إكمال المراحل السابقة.';
        }

        $approvals = CertificateApproval::query()
            ->where('certificate_id', $certificate->id)
            ->get()
            ->keyBy('approval_step');

        if ($approvalStep === 'training_manager_approval') {
            if (($approvals['center_approval']->decision ?? null) !== 'approved') {
                return 'يجب اعتماد المركز أولاً قبل اعتماد قسم التدريب.';
            }
        }

        if ($approvalStep === 'deputy_director_approval') {
            if (($approvals['center_approval']->decision ?? null) !== 'approved') {
                return 'يجب اعتماد المركز أولاً.';
            }

            if (($approvals['training_manager_approval']->decision ?? null) !== 'approved') {
                return 'يجب اعتماد قسم التدريب قبل توقيع نائب المدير العام.';
            }
        }

        if ($approvalStep === 'general_director_approval') {
            if (($approvals['center_approval']->decision ?? null) !== 'approved') {
                return 'يجب اعتماد المركز أولاً.';
            }

            if (($approvals['training_manager_approval']->decision ?? null) !== 'approved') {
                return 'يجب اعتماد قسم التدريب أولاً.';
            }

            if (($approvals['deputy_director_approval']->decision ?? null) !== 'approved') {
                return 'يجب توقيع نائب المدير العام قبل اعتماد المدير العام.';
            }
        }

        return null;
    }

    private function determineNextStatus($approvals): string
    {
        $centerApproved = $approvals->firstWhere('approval_step', 'center_approval')?->decision === 'approved';
        $managerApproved = $approvals->firstWhere('approval_step', 'training_manager_approval')?->decision === 'approved';
        $deputyApproved = $approvals->firstWhere('approval_step', 'deputy_director_approval')?->decision === 'approved';
        $generalApproved = $approvals->firstWhere('approval_step', 'general_director_approval')?->decision === 'approved';

        if ($centerApproved && $managerApproved && $deputyApproved && $generalApproved) {
            return 'approved';
        }

        if ($centerApproved && $managerApproved && $deputyApproved) {
            return 'pending_general_director_approval';
        }

        if ($centerApproved && $managerApproved) {
            return 'pending_deputy_approval';
        }

        if ($centerApproved) {
            return 'pending_training_approval';
        }

        return 'pending_center_approval';
    }
}
