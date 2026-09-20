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
        return $this->container->make(LogDriver::class);
    }

    /**
     * Create Kavenegar driver instance.
     */
    protected function createKavenegarDriver(): SmsDriverInterface
    {
        return $this->container->make(KavenegarDriver::class, [
            'client' => $this->container->make(KavenegarClient::class),
        ]);
    }

    /**
     * Create FarazSMS driver instance.
     */
    protected function createFarazsmsDriver(): SmsDriverInterface
    {
        return $this->container->make(FarazSmsDriver::class, [
            'client' => $this->container->make(FarazSmsClient::class),
        ]);
    }

    /**
     * Create Ghasedak driver instance.
     */
    protected function createGhasedakDriver(): SmsDriverInterface
    {
        return $this->container->make(GhasedakDriver::class, [
            'client' => $this->container->make(GhasedakClient::class),
        ]);
    }
}
