<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

class SignedPrintUrl
{
    /** مدة صلاحية روابط الطباعة بالساعات */
    public const EXPIRATION_HOURS = 24;

    public static function certificatePrint(int $id): string
    {
        return URL::temporarySignedRoute(
            'certificates.print',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function certificatePdf(int $id): string
    {
        return URL::temporarySignedRoute(
            'certificates.pdf',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function trainerCard(int $id): string
    {
        return URL::temporarySignedRoute(
            'trainers.card',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function trainerCardPdf(int $id): string
    {
        return URL::temporarySignedRoute(
            'trainers.card.pdf',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function traineeCard(int $id): string
    {
        return URL::temporarySignedRoute(
            'trainees.card',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function traineeCardPdf(int $id): string
    {
        return URL::temporarySignedRoute(
            'trainees.card.pdf',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function trainingCenterCertificate(int $id): string
    {
        return URL::temporarySignedRoute(
            'training-centers.certificate',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function trainingCenterCertificatePdf(int $id): string
    {
        return URL::temporarySignedRoute(
            'training-centers.certificate.pdf',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }
}
