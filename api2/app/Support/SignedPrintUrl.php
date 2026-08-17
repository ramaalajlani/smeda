<?php

namespace App\Support;

class SignedPrintUrl
{
    /** مدة صلاحية روابط الطباعة بالساعات */
    public const EXPIRATION_HOURS = 24;

    public static function certificatePrint(int $id): string
    {
        return BackendUrl::temporarySignedRoute(
            'certificates.print',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function certificatePdf(int $id): string
    {
        return BackendUrl::temporarySignedRoute(
            'certificates.pdf',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function trainerCard(int $id): string
    {
        return BackendUrl::temporarySignedRoute(
            'trainers.card',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function trainerCardPdf(int $id): string
    {
        return BackendUrl::temporarySignedRoute(
            'trainers.card.pdf',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function traineeCard(int $id): string
    {
        return BackendUrl::temporarySignedRoute(
            'trainees.card',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function traineeCardPdf(int $id): string
    {
        return BackendUrl::temporarySignedRoute(
            'trainees.card.pdf',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function trainingCenterCertificate(int $id): string
    {
        return BackendUrl::temporarySignedRoute(
            'training-centers.certificate',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }

    public static function trainingCenterCertificatePdf(int $id): string
    {
        return BackendUrl::temporarySignedRoute(
            'training-centers.certificate.pdf',
            now()->addHours(self::EXPIRATION_HOURS),
            ['id' => $id]
        );
    }
}
