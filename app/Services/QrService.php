<?php

namespace App\Services;

class QrService
{
    public function generatePayload(string $sessionId): string
    {
        $timestamp = now()->timestamp;
        $data = $sessionId . '|' . $timestamp;
        $signature = hash_hmac('sha256', $data, config('app.key'));
        return $data . '|' . $signature;
    }

    public function verifyPayload(string $payload, string $sessionId): bool
    {
        $parts = explode('|', $payload);
        if (count($parts) !== 3) return false;

        [$payloadSessionId, $timestamp, $signature] = $parts;

        // Reject replays older than 10 minutes
        if (abs(now()->timestamp - (int)$timestamp) > 600) return false;

        $expected = hash_hmac('sha256', "$payloadSessionId|$timestamp", config('app.key'));

        return hash_equals($expected, $signature) && $payloadSessionId === $sessionId;
    }
}
