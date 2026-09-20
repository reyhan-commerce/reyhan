<?php

declare(strict_types=1);

namespace App\Services\Integrations\FarazSms;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FarazSmsClient
{
    protected PendingRequest $http;

    public function __construct(
        protected string $apiKey,
        protected string $sender,
        protected string $otpPattern,
        ?PendingRequest $http = null
    ) {
        $this->http = $http ?? Http::baseUrl('https://edge.ippanel.com/api/v1/')
            ->timeout(5)
            ->withHeaders([
                'Authorization' => "AccessKey {$this->apiKey}",
            ]);
    }

    public function send(string $to, string $message): bool
    {
        try {
            $response = $this->http->post('sms/send/webservice/single', [
                'recipient' => [$to],
                'sender' => $this->sender,
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

            $response = $this->http->post('sms/pattern/normal/send', [
                'code' => $this->otpPattern,
                'sender' => $this->sender,
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
