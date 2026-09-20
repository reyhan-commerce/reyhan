<?php

declare(strict_types=1);

namespace App\Services\Captcha;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use JsonException;

class CaptchaService
{
    /**
     * Redis connection name for captcha challenge storage (DB 0).
     */
    protected const string REDIS_CONNECTION = 'default';

    /**
     * Challenge TTL in seconds (3 minutes).
     */
    protected const int TTL_SECONDS = 180;

    /**
     * Generate a modern PoW challenge for the "I am not a robot" interactive widget.
     *
     * @return array{key: string, salt: string, difficulty: int}
     *
     * @throws JsonException
     */
    public function generate(): array
    {
        $key = (string) Str::uuid();
        $salt = Str::random(16);
        // Moderate difficulty: 4 leading zero hex characters (fast in JS ~150ms-400ms, stops naive bots)
        $difficulty = 4;

        Redis::connection(self::REDIS_CONNECTION)->setex(
            "captcha:challenge:{$key}",
            self::TTL_SECONDS,
            json_encode([
                'salt' => $salt,
                'difficulty' => $difficulty,
                'verified' => false,
            ], JSON_THROW_ON_ERROR)
        );

        return [
            'key' => $key,
            'salt' => $salt,
            'difficulty' => $difficulty,
        ];
    }

    /**
     * Verify the client's computed solution when clicking "I am not a robot".
     *
     * @throws JsonException
     */
    public function solve(string $key, string $nonce, int $elapsedMs = 0): bool
    {
        $redisKey = "captcha:challenge:{$key}";
        $redis = Redis::connection(self::REDIS_CONNECTION);

        /** @var string|null $storedJson */
        $storedJson = $redis->get($redisKey);

        if ($storedJson === null) {
            return false;
        }

        /** @var array{salt: string, difficulty: int, verified: bool} $data */
        $data = json_decode($storedJson, true, 512, JSON_THROW_ON_ERROR);

        // Honeypot / human speed check: a human click + compute takes at least 200ms
        if ($elapsedMs < 100) {
            return false;
        }

        // Validate hash
        $targetPrefix = str_repeat('0', $data['difficulty']);
        $hash = hash('sha256', $data['salt'].$nonce);

        if (! str_starts_with($hash, $targetPrefix)) {
            return false;
        }

        // Mark as verified with 2-minute expiration for submitting the OTP form
        $data['verified'] = true;
        $redis->setex($redisKey, 120, json_encode($data, JSON_THROW_ON_ERROR));

        return true;
    }

    /**
     * Consume the verified captcha challenge token when submitting the action (e.g., OTP request).
     *
     * @throws JsonException
     */
    public function verify(?string $key, ?string $answer = null): bool
    {
        if (empty($key)) {
            return false;
        }

        $redisKey = "captcha:challenge:{$key}";
        $redis = Redis::connection(self::REDIS_CONNECTION);

        /** @var string|null $storedJson */
        $storedJson = $redis->get($redisKey);

        if ($storedJson === null) {
            return false;
        }

        // Delete immediately (one-time use token)
        $redis->del($redisKey);

        /** @var array{salt: string, difficulty: int, verified: bool} $data */
        $data = json_decode($storedJson, true, 512, JSON_THROW_ON_ERROR);

        return $data['verified'];
    }
}
