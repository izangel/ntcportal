<?php

namespace App\Support;

use Illuminate\Support\Str;

class AttendanceToken
{
    public static function generate(int $courseBlockId, int $ttlSeconds = 90): string
    {
        $payload = base64_encode(json_encode([
            'block' => $courseBlockId,
            'exp' => now()->addSeconds($ttlSeconds)->getTimestamp(),
            'nonce' => Str::random(8),
        ]));

        return $payload . '.' . static::sign($payload);
    }

    /**
     * Validate and decode a token.
     *
     * @return array{block:int, exp:int, nonce:string}|null Null when invalid/expired.
     */
    public static function validate(string $token): ?array
    {
        if (!is_string($token) || !Str::contains($token, '.')) {
            return null;
        }

        [$payload, $signature] = explode('.', $token, 2);

        if (!hash_equals(static::sign($payload), $signature)) {
            return null;
        }

        $data = json_decode(base64_decode($payload), true);

        if (!is_array($data) || !isset($data['block'], $data['exp'])) {
            return null;
        }

        if ((int) $data['exp'] < now()->getTimestamp()) {
            return null;
        }

        return [
            'block' => (int) $data['block'],
            'exp' => (int) $data['exp'],
            'nonce' => (string) ($data['nonce'] ?? ''),
        ];
    }

    private static function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, (string) config('app.key'));
    }
}
