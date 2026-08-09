<?php

namespace Tests\Qa;

final class QaHttp
{
    public function __construct(private string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function request(string $method, string $path, ?array $body = null, ?string $token = null): array
    {
        $url = str_starts_with($path, 'http') ? $path : $this->baseUrl.$path;
        $ch = curl_init($url);
        $headers = ['Accept: application/json', 'Content-Type: application/json'];
        if ($token) {
            $headers[] = 'Authorization: Bearer '.$token;
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADER => true,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
        }

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $header = is_string($raw) ? substr($raw, 0, $headerSize) : '';
        $bodyRaw = is_string($raw) ? substr($raw, $headerSize) : '';
        $json = null;
        if ($bodyRaw !== '') {
            $json = json_decode($bodyRaw, true);
        }

        return [
            'ok' => $errno === 0,
            'error' => $error,
            'status' => $status,
            'json' => $json,
            'raw' => $bodyRaw,
            'ms' => null,
        ];
    }

    public static function isAllowedStatus(int $status): bool
    {
        // Auth passed: success, validation, or missing resource
        return in_array($status, [200, 201, 202, 204, 404, 422], true);
    }

    public static function isDeniedStatus(int $status): bool
    {
        return in_array($status, [401, 403], true);
    }
}
