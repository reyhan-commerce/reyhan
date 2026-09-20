<?php

declare(strict_types=1);

namespace App\Services\Sms\Drivers;

use App\Services\Integrations\Ghasedak\GhasedakClient;
use App\Services\Sms\Contracts\SmsDriverInterface;

class GhasedakDriver implements SmsDriverInterface
{
    public function __construct(
        protected GhasedakClient $client
    ) {}

    public function send(string $to, string $message): bool
    {
        return $this->client->send($to, $message);
    }

    /**
     * @param  array<string, string>  $tokens
     */
    public function sendOtp(string $to, string $code, array $tokens = []): bool
    {
        return $this->client->sendOtp($to, $code, $tokens);
    }
}
