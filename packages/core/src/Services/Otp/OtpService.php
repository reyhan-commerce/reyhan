<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Otp;

use Reyhan\Core\Exceptions\Auth\OtpThrottledException;
use Reyhan\Core\Notifications\Auth\SendOtpNotification;
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
     * Maximum allowed verification attempts before invalidating OTP.
     */
    public const int MAX_ATTEMPTS = 5;

    /**
     * Lockout duration in seconds after exceeding max attempts (15 minutes).
     */
    public const int LOCKOUT_SECONDS = 900;

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
     * Compute fast constant-time HMAC hash for OTP code without CPU exhaustion.
     */
    protected function hashOtp(string $code): string
    {
        $key = (string) (config('app.key') ?: 'reyhan_secure_otp_salt_key');

        return hash_hmac('sha256', $code, $key);
    }

    /**
     * Generate 6-digit cryptographically secure OTP, store HMAC hash in Redis, and dispatch notification.
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

        // Fixed OTP in local/testing environment for seamless development and automated testing
        $code = app()->environment(['local', 'testing'])
            ? '123456'
            : (string) random_int(100000, 999999);

        $hashedCode = $this->hashOtp($code);

        $redis->setex("otp:code:{$mobile}", self::TTL_SECONDS, $hashedCode);
        $redis->setex($throttleKey, self::TTL_SECONDS, '1');
        $redis->del("otp:attempts:{$mobile}");

        Notification::route('sms', $mobile)
            ->notify(new SendOtpNotification($code));

        return [
            'code' => $code,
            'expires_in' => self::TTL_SECONDS,
        ];
    }

    /**
     * Verify the provided OTP code against Redis hash, enforce brute-force rate-limiting,
     * and automatically clear keys if valid.
     */
    public function verify(string $mobile, string $code): bool
    {
        $redis = Redis::connection(self::REDIS_CONNECTION);
        $storedHash = (string) $redis->get("otp:code:{$mobile}");

        if (empty($storedHash)) {
            return false;
        }

        $attemptsKey = "otp:attempts:{$mobile}";
        $attempts = (int) $redis->get($attemptsKey);

        if ($attempts >= self::MAX_ATTEMPTS) {
            // Lockout user and invalidate active code immediately
            $this->clear($mobile);
            $redis->setex("otp:throttle:{$mobile}", self::LOCKOUT_SECONDS, '1');

            return false;
        }

        $expectedHash = $this->hashOtp($code);

        if (! hash_equals($storedHash, $expectedHash)) {
            $newAttempts = (int) $redis->incr($attemptsKey);
            $redis->expire($attemptsKey, self::TTL_SECONDS);

            if ($newAttempts >= self::MAX_ATTEMPTS) {
                $this->clear($mobile);
                $redis->setex("otp:throttle:{$mobile}", self::LOCKOUT_SECONDS, '1');
            }

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

        return hash_equals($storedHash, $this->hashOtp($code));
    }

    /**
     * Consume (clear) OTP, attempts, and throttle keys from Redis upon successful verification.
     */
    public function clear(string $mobile): void
    {
        $redis = Redis::connection(self::REDIS_CONNECTION);
        $redis->del("otp:code:{$mobile}");
        $redis->del("otp:throttle:{$mobile}");
        $redis->del("otp:attempts:{$mobile}");
    }
}
