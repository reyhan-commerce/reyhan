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
        return $this->container->make(LogDriver::class);
    }

    /**
     * Create Kavenegar driver instance via container auto-wiring.
     */
    protected function createKavenegarDriver(): SmsDriverInterface
    {
        return $this->container->make(KavenegarDriver::class);
    }

    /**
     * Create FarazSMS driver instance via container auto-wiring.
     */
    protected function createFarazsmsDriver(): SmsDriverInterface
    {
        return $this->container->make(FarazSmsDriver::class);
    }

    /**
     * Create Ghasedak driver instance via container auto-wiring.
     */
    protected function createGhasedakDriver(): SmsDriverInterface
    {
        return $this->container->make(GhasedakDriver::class);
    }
}
