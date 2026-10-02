<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Integrations\FarazSms;

use Reyhan\Core\Settings\SmsSettings;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FarazSmsClient
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

        $apiKey = (string) ($this->settings->farazsms_api_key ?? '');

        return Http::baseUrl('https://edge.ippanel.com/api/v1/')
            ->timeout(5)
            ->withHeaders([
                'Authorization' => "AccessKey {$apiKey}",
            ]);
    }

    public function send(string $to, string $message): bool
    {
        try {
            $sender = (string) ($this->settings->farazsms_sender ?? '');
            $response = $this->http()->post('sms/send/webservice/single', [
                'recipient' => [$to],
                'sender' => $sender,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('[FarazSmsClient] Error sending SMS: '.$e->getMessage());

            return false;
        }
    }

    /**
     * @param  array<string, string>  $tokens
     */
    public function sendOtp(string $to, string $code, array $tokens = []): bool
    {
        try {
            $inputData = array_merge(['code' => $code], $tokens);
            $pattern = (string) ($this->settings->farazsms_otp_pattern ?? '');
            $sender = (string) ($this->settings->farazsms_sender ?? '');

            $response = $this->http()->post('sms/pattern/normal/send', [
                'code' => $pattern,
                'sender' => $sender,
                'recipient' => $to,
                'variable' => $inputData,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('[FarazSmsClient] Error sending OTP: '.$e->getMessage());

            return false;
        }
    }
}
