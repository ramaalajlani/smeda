<?php

namespace App\Services\Training;

use App\Models\Certificate;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\TrainingKit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EntityCodeGenerator
{
    public function nextCenterCode(): string
    {
        return $this->nextCode('TC', TrainingCenter::class, 3);
    }

    public function nextCourseCode(): string
    {
        return $this->nextCode('CRS', TrainingCourse::class, 4);
    }

    public function nextTrainerCode(): string
    {
        return $this->nextCode('TRN', Trainer::class, 3);
    }

    public function nextTraineeCode(): string
    {
        return $this->nextCode('TRA', Trainee::class, 3);
    }

    public function nextKitCode(): string
    {
        return $this->nextCode('KIT', TrainingKit::class, 4);
    }

    public function nextCertificateNumber(): string
    {
        return $this->nextCode('CERT', Certificate::class, 6);
    }

    public function nextReferenceNumber(int $sequence): string
    {
        return 'REF-' . now()->format('Ymd') . '-' . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public function nextVerificationCode(): string
    {
        return strtoupper(Str::random(12));
    }

    public function nextRequestNumber(string $prefix): string
    {
        return $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }

    private function nextCode(string $prefix, string $modelClass, int $padLength): string
    {
        return DB::transaction(function () use ($prefix, $modelClass, $padLength) {
            $nextId = ($modelClass::query()->lockForUpdate()->max('id') ?? 0) + 1;

            return $prefix . '-' . str_pad((string) $nextId, $padLength, '0', STR_PAD_LEFT);
        });
    }
}
