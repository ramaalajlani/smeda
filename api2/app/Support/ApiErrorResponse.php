<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

class ApiErrorResponse
{
    public static function payload(Throwable $e, string $userMessage, string $logContext): array
    {
        Log::error($logContext, [
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        $payload = [
            'message' => $userMessage,
        ];

        if (config('app.debug')) {
            $payload['error'] = $e->getMessage();
        }

        return $payload;
    }
}
