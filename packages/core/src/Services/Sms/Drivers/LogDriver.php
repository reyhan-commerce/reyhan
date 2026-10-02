<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Sms\Drivers;

use Reyhan\Core\Services\Sms\Contracts\SmsDriverInterface;
use Illuminate\Support\Facades\Log;

class LogDriver implements SmsDriverInterface
{
    /**
     * Send standard SMS message by logging.
     */
    public function send(string $to, string $message): bool
    {
        Log::info("[SMS] Send to: {$to} | Message: {$message}");

        return true;
    }

    /**
     * Send OTP verification code by logging.
     *
     * @param  array<string, string>  $tokens
     */
    public function sendOtp(string $to, string $code, array $tokens = []): bool
    {
        Log::info("[SMS OTP] Sent to: {$to} | Code: {$code} | Tokens: ".json_encode($tokens));

        return true;
    }
}
