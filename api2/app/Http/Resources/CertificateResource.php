<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ExposesBranchScopeFields;
use App\Support\BackendUrl;
use App\Support\SignedPrintUrl;use App\Support\TrainingDataScope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
{
    use ExposesBranchScopeFields;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $canSeeSecrets = $user && TrainingDataScope::canViewCertificateSecrets($user, $this->resource);
        $canSeeTraineeSensitive = $user && TrainingDataScope::canViewTraineeSensitive($user, $this->trainee_id);
        $canSeeTrainerContact = $user && TrainingDataScope::canViewTrainerContact(
            $user,
            $this->trainer_id,
            $this->training_center_id
        );
        $canSeeCenterContact = $user && (
            TrainingDataScope::hasBroadTrainingReadAccess($user)
            || ($user->isCenterUser() && $user->belongsToCenter($this->training_center_id))
        );

        $antiFakeHash = $canSeeSecrets ? $this->buildAntiFakeHash() : null;

        return [
            'id' => $this->id,

            'certificate_number' => $this->certificate_number,
            'certificate_code' => $this->certificate_code,
            'reference_number' => $this->reference_number,
            'verification_code' => $this->when($canSeeSecrets, $this->verification_code),

            'certificate_type' => $this->certificate_type,
            'certificate_type_label' => \App\Support\CertificateType::label($this->certificate_type),
            'center_code' => $this->center_code,
            'trainer_code' => $this->trainer_code,
            'kit_code' => $this->kit_code,
            'course_code' => $this->course_code,
            'trainee_code' => $this->trainee_code,
            'result' => $this->result,
            'score' => $this->score,
            'hours_awarded' => $this->hours_awarded,
            'training_hours' => $this->training_hours ?? $this->hours_awarded,

            'status' => $this->status,
            'issue_date' => optional($this->issue_date)->format('Y-m-d'),
            'issued_at' => optional($this->issued_at ?? $this->issue_date)->format('Y-m-d H:i:s'),
            'certificate_date' => optional($this->certificate_date)->format('Y-m-d'),

            'is_verified' => (bool) $this->is_verified,
            'verified_at' => optional($this->verified_at)->format('Y-m-d H:i:s'),
            'notes' => $this->notes,

            /*
            |--------------------------------------------------------------------------
            | Files / URLs
            |--------------------------------------------------------------------------
            */
            'qr_code_path' => $this->qr_code_path,
            'certificate_file_path' => $this->certificate_file_path,

            'qr_code_url' => ($this->status === 'approved' && (bool) $this->is_verified && $this->certificate_code)
                ? BackendUrl::to('certificates/' . rawurlencode((string) $this->certificate_code) . '/qr')
                : ($this->qr_code_path ? url($this->qr_code_path) : null),

            'qr_url' => $this->qr_url,
            'view_url' => $this->certificate_code
                ? BackendUrl::to('verify-certificate/' . rawurlencode((string) $this->certificate_code))
                : null,
            'public_print_url' => ($this->status === 'approved' && $this->is_verified && $this->certificate_code)
                ? BackendUrl::to('certificates/' . rawurlencode((string) $this->certificate_code) . '/print')
                : null,

            'printable_url' => SignedPrintUrl::certificatePrint($this->id),
            'pdf_url' => SignedPrintUrl::certificatePdf($this->id),

            'verify_url' => $canSeeSecrets && $this->certificate_code
                ? BackendUrl::to('verify-certificate/' . rawurlencode((string) $this->certificate_code))
                : ($canSeeSecrets && $this->verification_code
                    ? BackendUrl::to('certificates/verify?code=' . urlencode((string) $this->verification_code) . '&hash=' . urlencode((string) $antiFakeHash))
                    : null),

            /*
            |--------------------------------------------------------------------------
            | Anti Fake
            |--------------------------------------------------------------------------
            */
            'anti_fake_hash' => $this->when($canSeeSecrets, $antiFakeHash),
            'anti_fake_enabled' => $canSeeSecrets,

            /*
            |--------------------------------------------------------------------------
            | Relationships IDs
            |--------------------------------------------------------------------------
            */
            'trainee_id' => $this->trainee_id,
            'training_center_id' => $this->training_center_id,
            'trainer_id' => $this->trainer_id,
            'training_kit_id' => $this->training_kit_id,
            'training_program_id' => $this->training_program_id,
            'training_course_id' => $this->training_course_id,

            ...$this->certificateBranchScopeFields(),

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */
            'trainee' => $this->whenLoaded('trainee', function () use ($canSeeTraineeSensitive) {
                $traineeId = (int) ($this->trainee?->id ?? 0);

                return [
                    'id' => $this->trainee?->id,
                    'name' => $this->trainee?->name,
                    'trainee_code' => $this->trainee?->trainee_code,
                    'national_id' => $this->when($canSeeTraineeSensitive, $this->trainee?->national_id),
                    'status' => $this->trainee?->status,
                    'card_url' => $traineeId > 0 ? SignedPrintUrl::traineeCard($traineeId) : null,
                    'pdf_url' => $traineeId > 0 ? SignedPrintUrl::traineeCardPdf($traineeId) : null,
                ];
            }),

            'training_center' => $this->whenLoaded('trainingCenter', function () use ($canSeeCenterContact) {
                return [
                    'id' => $this->trainingCenter?->id,
                    'name' => $this->trainingCenter?->name,
                    'code' => $this->trainingCenter?->code,
                    'city' => $this->trainingCenter?->city,
                    'address' => $this->when($canSeeCenterContact, $this->trainingCenter?->address),
                    'phone' => $this->when($canSeeCenterContact, $this->trainingCenter?->phone),
                    'email' => $this->when($canSeeCenterContact, $this->trainingCenter?->email),
                ];
            }),

            'trainer' => $this->whenLoaded('trainer', function () use ($canSeeTrainerContact) {
                return [
                    'id' => $this->trainer?->id,
                    'name' => $this->trainer?->name,
                    'trainer_code' => $this->trainer?->trainer_code,
                    'phone' => $this->when($canSeeTrainerContact, $this->trainer?->phone),
                    'email' => $this->when($canSeeTrainerContact, $this->trainer?->email),
                ];
            }),

            'training_kit' => $this->whenLoaded('trainingKit', function () {
                return [
                    'id' => $this->trainingKit?->id,
                    'name' => $this->trainingKit?->name,
                    'code' => $this->trainingKit?->code,
                    'level' => $this->trainingKit?->level,
                    'hours' => $this->trainingKit?->hours,
                ];
            }),

            'training_program' => $this->whenLoaded('trainingProgram', function () {
                return [
                    'id' => $this->trainingProgram?->id,
                    'name' => $this->trainingProgram?->name,
                    'code' => $this->trainingProgram?->code,
                    'description' => $this->trainingProgram?->description,
                    'status' => $this->trainingProgram?->status,
                ];
            }),

            'training_course' => $this->whenLoaded('trainingCourse', function () {
                return [
                    'id' => $this->trainingCourse?->id,
                    'course_code' => $this->trainingCourse?->course_code,
                    'title' => $this->trainingCourse?->title,
                    'delivery_mode' => $this->trainingCourse?->delivery_mode,
                    'start_date' => optional($this->trainingCourse?->start_date)->format('Y-m-d'),
                    'end_date' => optional($this->trainingCourse?->end_date)->format('Y-m-d'),
                    'planned_hours' => $this->trainingCourse?->planned_hours,
                    'actual_hours' => $this->trainingCourse?->actual_hours,
                    'status' => $this->trainingCourse?->status,
                ];
            }),

            'approvals' => $this->whenLoaded('approvals', function () {
                return $this->approvals->map(function ($approval) {
                    $payload = [
                        'id' => $approval->id,
                        'certificate_id' => $approval->certificate_id,
                        'approved_by' => $approval->approved_by,
                        'approval_step' => $approval->approval_step,
                        'decision' => $approval->decision,
                        'decision_at' => optional($approval->decision_at)->format('Y-m-d H:i:s'),
                        'notes' => $approval->notes,
                    ];

                    if ($approval->relationLoaded('electronicSignature') && $approval->electronicSignature) {
                        $sig = $approval->electronicSignature;
                        $payload['electronic_signature'] = [
                            'id' => $sig->id,
                            'verification_code' => $sig->verification_code,
                            'signer_name' => $sig->signer_name,
                            'signer_title' => $sig->signer_title,
                            'role_key' => $sig->role_key,
                            'signed_at' => optional($sig->signed_at)->format('Y-m-d H:i:s'),
                            'signature_image_hash' => $sig->signature_image_hash,
                            'snapshot_image_url' => $sig->signature_image_path
                                ? url('/api/electronic-signatures/' . $sig->id . '/snapshot-image')
                                : null,
                        ];
                    }

                    return $payload;
                })->values();
            }),

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function certificateBranchScopeFields(): array
    {
        $branchId = $this->branch_id ?? $this->trainingCourse?->branch_id;
        $governorateId = $this->governorate_id ?? $this->trainingCourse?->governorate_id;

        return [
            'branch_id' => $branchId,
            'branch_name' => $this->branch?->name ?? $this->trainingCourse?->branch?->name,
            'governorate_id' => $governorateId,
            'governorate_name' => $this->governorate?->name_ar ?? $this->trainingCourse?->governorate?->name_ar,
        ];
    }

    /**
     * Build anti-fake hash from current certificate data.
     */
    private function buildAntiFakeHash(): string
    {
        $payload = implode('|', [
            (string) $this->id,
            (string) $this->certificate_number,
            (string) $this->certificate_code,
            (string) $this->reference_number,
            (string) $this->verification_code,
            (string) $this->trainee_id,
            (string) $this->training_course_id,
            (string) $this->certificate_type,
            (string) ($this->training_hours ?? $this->hours_awarded),
            (string) optional($this->issue_date)->format('Y-m-d'),
            (string) $this->status,
        ]);

        return hash_hmac('sha256', $payload, (string) config('app.key'));
    }
}