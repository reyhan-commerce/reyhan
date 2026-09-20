<?php

declare(strict_types=1);

namespace App\Services\Integrations\Kavenegar;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KavenegarClient
{
    public function __construct(
        protected string $apiKey,
        protected string $sender,
        protected string $otpPattern
    ) {}

    public function send(string $to, string $message): bool
    {
        try {
            $url = "https://api.kavenegar.com/v1/{$this->apiKey}/sms/send.json";
            $response = Http::timeout(5)->asForm()->post($url, [
                'receptor' => $to,
                'sender' => $this->sender,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('[KavenegarClient] Error sending SMS: '.$e->getMessage());

            return false;
        }
    }

    /**
     * @param  array<string, string>  $tokens
     */
    public function sendOtp(string $to, string $code, array $tokens = []): bool
    {
        try {
            $url = "https://api.kavenegar.com/v1/{$this->apiKey}/verify/lookup.json";
            $response = Http::timeout(5)->asForm()->post($url, [
                'receptor' => $to,
                'token' => $code,
                'template' => $this->otpPattern,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('[KavenegarClient] Error sending OTP: '.$e->getMessage());

            return false;
        }
    }
}
