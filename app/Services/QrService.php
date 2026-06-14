<?php

namespace App\Services;

class QrService
{
    public function generatePayload(string $sessionId): string
    {
        $timestamp = now()->timestamp;
        $data = $sessionId . '|' . $timestamp;
        $key = config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        $signature = hash_hmac('sha256', $data, $key);
        return $data . '|' . $signature;
    }

    public function verifyPayload(string $payload, string $sessionId): bool
    {
        $payload = trim($payload);
        $parts = explode('|', $payload);
        if (count($parts) !== 3) return false;

        [$payloadSessionId, $timestamp, $signature] = array_map('trim', $parts);

        // Reject replays older than 10 minutes
        if (abs(now()->timestamp - (int)$timestamp) > 600) return false;

        $key = config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $expected = hash_hmac('sha256', "$payloadSessionId|$timestamp", $key);

        return hash_equals($expected, $signature) && $payloadSessionId === $sessionId;
    }
}
