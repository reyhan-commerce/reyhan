<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Sms\Drivers;

use Reyhan\Core\Services\Integrations\Kavenegar\KavenegarClient;
use Reyhan\Core\Services\Sms\Contracts\SmsDriverInterface;

class KavenegarDriver implements SmsDriverInterface
{
    public function __construct(
        protected KavenegarClient $client
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
