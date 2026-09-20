<?php

declare(strict_types=1);

namespace App\Services\Sms;

use App\Services\Integrations\FarazSms\FarazSmsClient;
use App\Services\Integrations\Ghasedak\GhasedakClient;
use App\Services\Integrations\Kavenegar\KavenegarClient;
use App\Services\Sms\Contracts\SmsDriverInterface;
use App\Services\Sms\Drivers\FarazSmsDriver;
use App\Services\Sms\Drivers\GhasedakDriver;
use App\Services\Sms\Drivers\KavenegarDriver;
use App\Services\Sms\Drivers\LogDriver;
use Illuminate\Support\Manager;

class SmsManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        /** @var string $driver */
        $driver = $this->config->get('sms.default', 'log');

        return $driver;
    }

    /**
     * Create Log driver instance.
     */
    protected function createLogDriver(): SmsDriverInterface
    {
        return new LogDriver;
    }

    /**
     * Create Kavenegar driver instance.
     */
    protected function createKavenegarDriver(): SmsDriverInterface
    {
        /** @var array{api_key?: string, sender?: string, otp_pattern?: string} $config */
        $config = $this->config->get('sms.drivers.kavenegar', []);

        $client = new KavenegarClient(
            apiKey: (string) ($config['api_key'] ?? ''),
            sender: (string) ($config['sender'] ?? ''),
            otpPattern: (string) ($config['otp_pattern'] ?? '')
        );

        return new KavenegarDriver($client);
    }

    /**
     * Create FarazSMS driver instance.
     */
    protected function createFarazsmsDriver(): SmsDriverInterface
    {
        /** @var array{api_key?: string, sender?: string, otp_pattern?: string} $config */
        $config = $this->config->get('sms.drivers.farazsms', []);

        $client = new FarazSmsClient(
            apiKey: (string) ($config['api_key'] ?? ''),
            sender: (string) ($config['sender'] ?? ''),
            otpPattern: (string) ($config['otp_pattern'] ?? '')
        );

        return new FarazSmsDriver($client);
    }

    /**
     * Create Ghasedak driver instance.
     */
    protected function createGhasedakDriver(): SmsDriverInterface
    {
        /** @var array{api_key?: string, sender?: string, otp_template?: string} $config */
        $config = $this->config->get('sms.drivers.ghasedak', []);

        $client = new GhasedakClient(
            apiKey: (string) ($config['api_key'] ?? ''),
            sender: (string) ($config['sender'] ?? ''),
            otpTemplate: (string) ($config['otp_template'] ?? '')
        );

        return new GhasedakDriver($client);
    }
}
