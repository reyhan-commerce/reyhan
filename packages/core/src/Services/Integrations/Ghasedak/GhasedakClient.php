<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Integrations\Ghasedak;

use Reyhan\Core\Settings\SmsSettings;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhasedakClient
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

        $apiKey = (string) ($this->settings->ghasedak_api_key ?? '');

        return Http::baseUrl('https://api.ghasedak.me/v2/')
            ->timeout(5)
            ->withHeaders([
                'apikey' => $apiKey,
            ])
            ->asForm();
    }

    public function send(string $to, string $message): bool
    {
        try {
            $sender = (string) ($this->settings->ghasedak_sender ?? '');
            $response = $this->http()->post('sms/send/simple', [
                'receptor' => $to,
                'message' => $message,
                'linenumber' => $sender,
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
            $template = (string) ($this->settings->ghasedak_otp_template ?? '');
            $response = $this->http()->post('verification/send/simple', [
                'receptor' => $to,
                'type' => '1',
                'template' => $template,
                'param1' => $code,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('[GhasedakClient] Error sending OTP: '.$e->getMessage());

            return false;
        }
    }
}
