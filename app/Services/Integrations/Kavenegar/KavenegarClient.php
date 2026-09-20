<?php

declare(strict_types=1);

namespace App\Services\Integrations\Kavenegar;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KavenegarClient
{
    protected PendingRequest $http;

    public function __construct(
        protected string $apiKey,
        protected string $sender,
        protected string $otpPattern,
        ?PendingRequest $http = null
    ) {
        $this->http = $http ?? Http::baseUrl("https://api.kavenegar.com/v1/{$this->apiKey}/")
            ->timeout(5)
            ->asForm();
    }

    public function send(string $to, string $message): bool
    {
        try {
            $response = $this->http->post('sms/send.json', [
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
            $response = $this->http->post('verify/lookup.json', [
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
