<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Integrations\Kavenegar;

use Reyhan\Core\Settings\SmsSettings;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KavenegarClient
{
    public function __construct(
        protected SmsSettings $settings,
        protected ?PendingRequest $http = null
    ) {}

    protected function http(): PendingRequest
    {
        if ($this->http !== null) {
            return $this->http;
        }

        $apiKey = (string) ($this->settings->kavenegar_api_key ?? '');

        return Http::baseUrl("https://api.kavenegar.com/v1/{$apiKey}/")
            ->timeout(5)
            ->asForm();
    }

    public function send(string $to, string $message): bool
    {
        try {
            $sender = (string) ($this->settings->kavenegar_sender ?? '');
            $response = $this->http()->post('sms/send.json', [
                'receptor' => $to,
                'sender' => $sender,
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
            $template = (string) ($this->settings->kavenegar_otp_pattern ?? '');
            $response = $this->http()->post('verify/lookup.json', [
                'receptor' => $to,
                'token' => $code,
                'template' => $template,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('[KavenegarClient] Error sending OTP: '.$e->getMessage());

            return false;
        }
    }
}
