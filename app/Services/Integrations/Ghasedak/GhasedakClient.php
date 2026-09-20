<?php

declare(strict_types=1);

namespace App\Services\Integrations\Ghasedak;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhasedakClient
{
    protected PendingRequest $http;

    public function __construct(
        protected string $apiKey,
        protected string $sender,
        protected string $otpTemplate,
        ?PendingRequest $http = null
    ) {
        $this->http = $http ?? Http::baseUrl('https://api.ghasedak.me/v2/')
            ->timeout(5)
            ->withHeaders([
                'apikey' => $this->apiKey,
            ])
            ->asForm();
    }

    public function send(string $to, string $message): bool
    {
        try {
            $response = $this->http->post('sms/send/simple', [
                'receptor' => $to,
                'message' => $message,
                'linenumber' => $this->sender,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('[GhasedakClient] Error sending SMS: '.$e->getMessage());

            return false;
        }
    }

    /**
     * @param  array<string, string>  $tokens
     */
    public function sendOtp(string $to, string $code, array $tokens = []): bool
    {
        try {
            $response = $this->http->post('verification/send/simple', [
                'receptor' => $to,
                'type' => '1',
                'template' => $this->otpTemplate,
                'param1' => $code,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('[GhasedakClient] Error sending OTP: '.$e->getMessage());

            return false;
        }
    }
}
