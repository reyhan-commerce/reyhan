<?php

declare(strict_types=1);

namespace App\Services\Sms;

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

        return new KavenegarDriver(
            apiKey: (string) ($config['api_key'] ?? ''),
            sender: (string) ($config['sender'] ?? ''),
            otpPattern: (string) ($config['otp_pattern'] ?? '')
        );
    }

    /**
     * Create FarazSMS driver instance.
     */
    protected function createFarazsmsDriver(): SmsDriverInterface
    {
        /** @var array{api_key?: string, sender?: string, otp_pattern?: string} $config */
        $config = $this->config->get('sms.drivers.farazsms', []);

        return new FarazSmsDriver(
            apiKey: (string) ($config['api_key'] ?? ''),
            sender: (string) ($config['sender'] ?? ''),
            otpPattern: (string) ($config['otp_pattern'] ?? '')
        );
    }

    /**
     * Create Ghasedak driver instance.
     */
    protected function createGhasedakDriver(): SmsDriverInterface
    {
        /** @var array{api_key?: string, sender?: string, otp_template?: string} $config */
        $config = $this->config->get('sms.drivers.ghasedak', []);

        return new GhasedakDriver(
            apiKey: (string) ($config['api_key'] ?? ''),
            sender: (string) ($config['sender'] ?? ''),
            otpTemplate: (string) ($config['otp_template'] ?? '')
        );
    }
}
