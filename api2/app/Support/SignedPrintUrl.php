<?php

namespace App\Support;

class SignedPrintUrl
{
    /** مدة صلاحية روابط الطباعة بالساعات */
    public const EXPIRATION_HOURS = 24;

    public static function certificatePrint(int $id): ?string
    {
        return self::safeRoute('certificates.print', ['id' => $id]);
    }

    public static function certificatePdf(int $id): ?string
    {
        return self::safeRoute('certificates.pdf', ['id' => $id]);
    }

    public static function trainerCard(int $id): ?string
    {
        return self::safeRoute('trainers.card', ['id' => $id]);
    }

    public static function trainerCardPdf(int $id): ?string
    {
        return self::safeRoute('trainers.card.pdf', ['id' => $id]);
    }

    public static function traineeCard(int $id): ?string
    {
        return self::safeRoute('trainees.card', ['id' => $id]);
    }

    public static function traineeCardPdf(int $id): ?string
    {
        return self::safeRoute('trainees.card.pdf', ['id' => $id]);
    }

    public static function trainingCenterCertificate(int $id): ?string
    {
        return self::safeRoute('training-centers.certificate', ['id' => $id]);
    }

    public static function trainingCenterCertificatePdf(int $id): ?string
    {
        return self::safeRoute('training-centers.certificate.pdf', ['id' => $id]);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private static function safeRoute(string $name, array $parameters): ?string
    {
        try {
            return BackendUrl::temporarySignedRoute(
                $name,
                now()->addHours(self::EXPIRATION_HOURS),
                $parameters
            );
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
