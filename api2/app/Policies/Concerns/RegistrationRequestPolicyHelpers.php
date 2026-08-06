<?php



namespace App\Policies\Concerns;



use App\Models\User;

use App\Support\TrainingDataScope;



trait RegistrationRequestPolicyHelpers

{

    protected function canAccessRegistrationRequest(

        ?User $user,

        ?int $branchId,

        ?int $submittedByUserId,

        ?int $trainingCenterId = null,

        ?int $trainingCourseId = null,

    ): bool {

        return TrainingDataScope::canAccessRegistrationRequest(

            $user,

            $branchId,

            $submittedByUserId,

            $trainingCenterId,

            $trainingCourseId

        );

    }

}

