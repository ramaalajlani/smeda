<?php

namespace App\Support;

use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\User;

class RegistrationApprovalLinker
{
    public static function linkUserToCenter(User $user, TrainingCenter $center): void
    {
        $user->update([
            'training_center_id' => $center->id,
            'entity_type' => 'center_user',
        ]);

        if (!$user->hasRole('center_user')) {
            $user->assignRole('center_user');
        }
    }

    public static function linkUserToTrainer(User $user, Trainer $trainer): void
    {
        $user->update([
            'trainer_id' => $trainer->id,
            'training_center_id' => $trainer->training_center_id,
            'entity_type' => 'trainer_user',
        ]);

        if (!$user->hasRole('trainer_user')) {
            $user->assignRole('trainer_user');
        }
    }

    public static function linkUserToTrainee(User $user, Trainee $trainee): void
    {
        $user->update([
            'trainee_id' => $trainee->id,
            'entity_type' => 'trainee_user',
        ]);

        if (!$user->hasRole('trainee_user')) {
            $user->assignRole('trainee_user');
        }
    }
}
