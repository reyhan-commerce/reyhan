<?php

declare(strict_types=1);

namespace App\Services\Otp;

use App\Exceptions\Auth\OtpThrottledException;
use App\Notifications\Auth\SendOtpNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;

class OtpService
{
    /**
     * Redis connection name.
     */
    protected const REDIS_CONNECTION = 'default';

    /**
     * OTP token and throttle TTL in seconds (2 minutes).
     */
    protected const TTL_SECONDS = 120;

    /**
     * Check if an OTP request is currently throttled for the given mobile.
     */
    public function isThrottled(string $mobile): bool
    {
        $redis = Redis::connection(self::REDIS_CONNECTION);

        return (bool) $redis->get("otp:throttle:{$mobile}");
    }

    /**
     * Get remaining throttle TTL in seconds.
     */
    public function getThrottleTtl(string $mobile): int
    {
        $redis = Redis::connection(self::REDIS_CONNECTION);
        $ttl = (int) $redis->ttl("otp:throttle:{$mobile}");

        return max($ttl, 0);
    }

    /**
     * Generate 5-digit cryptographically secure OTP, store hashed in Redis, and dispatch notification.
     * Throws OtpThrottledException if called within throttle window.
     *
     * @return array{code: string, expires_in: int}
     *
     * @throws OtpThrottledException
     */
    public function generateAndSend(string $mobile): array
    {
        $redis = Redis::connection(self::REDIS_CONNECTION);
        $throttleKey = "otp:throttle:{$mobile}";

        // Enforce throttle within generateAndSend
        if ($redis->get($throttleKey)) {
            $ttl = max((int) $redis->ttl($throttleKey), 0);
            throw new OtpThrottledException($ttl);
        }

        $code = (string) random_int(10000, 99999);
        $hashedCode = Hash::make($code);

        $redis->setex("otp:code:{$mobile}", self::TTL_SECONDS, $hashedCode);
        $redis->setex($throttleKey, self::TTL_SECONDS, '1');

        Notification::route('sms', $mobile)
            ->notify(new SendOtpNotification($code));

        return [
            'code' => $code,
            'expires_in' => self::TTL_SECONDS,
        ];
    }

    /**
     * Verify the provided OTP code against Redis hash, and automatically clear keys if valid.
     */
    public function verify(string $mobile, string $code): bool
    {
        $redis = Redis::connection(self::REDIS_CONNECTION);
        $storedHash = (string) $redis->get("otp:code:{$mobile}");

        if (empty($storedHash)) {
            return false;
        }

        if (! Hash::check($code, $storedHash)) {
            return false;
        }

        // Verification succeeded: automatically invalidate code and throttle in Redis
        $this->clear($mobile);

        return true;
    }

    /**
     * Check if the provided OTP code matches without clearing it.
     */
    public function check(string $mobile, string $code): bool
    {
        $redis = Redis::connection(self::REDIS_CONNECTION);
        $storedHash = (string) $redis->get("otp:code:{$mobile}");

        if (empty($storedHash)) {
            return false;
        }

        return Hash::check($code, $storedHash);
    }

    /**
     * Consume (clear) OTP and throttle keys from Redis upon successful verification.
     */
    public function clear(string $mobile): void
    {
        $redis = Redis::connection(self::REDIS_CONNECTION);
        $redis->del("otp:code:{$mobile}");
        $redis->del("otp:throttle:{$mobile}");
    }
}
