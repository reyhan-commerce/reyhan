<?php

declare(strict_types=1);

namespace App\Services\Sms\Drivers;

use App\Services\Sms\Contracts\SmsDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhasedakDriver implements SmsDriverInterface
{
    public function __construct(
        protected string $apiKey,
        protected string $sender,
        protected string $otpTemplate
    ) {}

    public function send(string $to, string $message): bool
    {
        try {
            $response = Http::timeout(5)->withHeaders([
                'apikey' => $this->apiKey,
            ])->asForm()->post('https://api.ghasedak.me/v2/sms/send/simple', [
                'receptor' => $to,
                'message' => $message,
                'linenumber' => $this->sender,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('[GhasedakDriver] Error sending SMS: '.$e->getMessage());

            return false;
        }
    }

    public function sendOtp(string $to, string $code, array $tokens = []): bool
    {
        try {
            $response = Http::timeout(5)->withHeaders([
                'apikey' => $this->apiKey,
            ])->asForm()->post('https://api.ghasedak.me/v2/verification/send/simple', [
                'receptor' => $to,
                'type' => '1',
                'template' => $this->otpTemplate,
                'param1' => $code,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('[GhasedakDriver] Error sending OTP: '.$e->getMessage());

            return false;
        }
    }
}
