<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Policies\Concerns\TrainingPolicyHelpers;
use App\Support\TrainingDataScope;

class CertificatePolicy
{
    use GrantsPlatformAdminFullAccess;
    use TrainingPolicyHelpers;

    public function viewAny(?User $user): bool
    {
        return $this->hasPermission($user, 'view_certificates');
    }

    public function view(?User $user, Certificate $certificate): bool
    {
        if (!$this->viewAny($user)) {
            return false;
        }

        return TrainingDataScope::scopeCertificates(
            Certificate::query()->whereKey($certificate->id),
            $user
        )->exists();
    }

    public function issue(?User $user): bool
    {
        return $this->hasPermission($user, 'issue_certificates');
    }

    public function approveCenter(?User $user, Certificate $certificate): bool
    {
        if (!$this->hasPermission($user, 'approve_center_certificates')) {
            return false;
        }

        if ($user->isCenterUser()) {
            return $user->belongsToCenter($certificate->training_center_id);
        }

        return $this->hasUnrestricted($user);
    }

    public function approveTraining(?User $user, Certificate $certificate): bool
    {
        return $this->hasPermission($user, 'approve_training_certificates')
            && ($this->hasUnrestricted($user) || $this->hasBroadRead($user));
    }

    public function approveDeputy(?User $user, Certificate $certificate): bool
    {
        return $this->hasPermission($user, 'approve_deputy_certificates');
    }

    public function approveGeneralDirector(?User $user, Certificate $certificate): bool
    {
        return $this->hasPermission($user, 'approve_general_director_certificates')
            || $user?->hasRole(['general_director', 'admin', 'super_admin']);
    }

    public function approve(?User $user, Certificate $certificate, string $approvalStep): bool
    {
        if (!$this->view($user, $certificate)) {
            return false;
        }

        return match ($approvalStep) {
            'center_approval' => $this->approveCenter($user, $certificate),
            'training_manager_approval' => $this->approveTraining($user, $certificate),
            'deputy_director_approval' => $this->approveDeputy($user, $certificate),
            'general_director_approval' => $this->approveGeneralDirector($user, $certificate),
            default => false,
        };
    }

    public function verify(?User $user): bool
    {
        return true;
    }

    public function print(?User $user, Certificate $certificate): bool
    {
        return $this->hasPermission($user, 'print_certificates')
            && $this->view($user, $certificate);
    }
}
