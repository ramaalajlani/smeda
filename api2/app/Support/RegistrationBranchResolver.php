<?php



namespace App\Support;



use App\Models\TrainingCenter;

use App\Models\TrainingCourse;

use App\Models\User;



class RegistrationBranchResolver

{

    /** @return array{branch_id: int|null, governorate_id: int|null} */

    public static function fromUser(?User $user): array

    {

        return [

            'branch_id' => $user?->branch_id,

            'governorate_id' => $user?->governorate_id,

        ];

    }



    /** @return array{branch_id: int|null, governorate_id: int|null} */

    public static function fromTrainingCenter(int|string|null $centerId): array

    {

        if (blank($centerId)) {

            return ['branch_id' => null, 'governorate_id' => null];

        }



        $center = TrainingCenter::query()->find($centerId);



        return [

            'branch_id' => $center?->branch_id,

            'governorate_id' => $center?->governorate_id,

        ];

    }



    /** @return array{branch_id: int|null, governorate_id: int|null} */

    public static function fromTrainingCourse(int|string|null $courseId): array

    {

        if (blank($courseId)) {

            return ['branch_id' => null, 'governorate_id' => null];

        }



        $course = TrainingCourse::query()->find($courseId);



        return [

            'branch_id' => $course?->branch_id,

            'governorate_id' => $course?->governorate_id,

        ];

    }

}

