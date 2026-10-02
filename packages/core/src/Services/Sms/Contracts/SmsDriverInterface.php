<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Sms\Contracts;

interface SmsDriverInterface
{
    /**
     * Send standard SMS message to a mobile number.
     */
    public function send(string $to, string $message): bool;

    /**
     * Send pattern/template based OTP verification code.
     *
     * @param  array<string, string>  $tokens
     */
    public function sendOtp(string $to, string $code, array $tokens = []): bool;
}
