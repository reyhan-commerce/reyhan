<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Sms;

use Reyhan\Core\Services\Sms\Contracts\SmsDriverInterface;
use Reyhan\Core\Services\Sms\Drivers\FarazSmsDriver;
use Reyhan\Core\Services\Sms\Drivers\GhasedakDriver;
use Reyhan\Core\Services\Sms\Drivers\KavenegarDriver;
use Reyhan\Core\Services\Sms\Drivers\LogDriver;
use Reyhan\Core\Settings\SmsSettings;
use Illuminate\Support\Manager;

class SmsManager extends Manager
{
    /**
     * Get the default driver name dynamically from SmsSettings.
     */
    public function getDefaultDriver(): string
    {
        try {
            /** @var SmsSettings $settings */
            $settings = $this->container->make(SmsSettings::class);

            return $settings->active_driver;
        } catch (\Throwable) {
            /** @var string $driver */
            $driver = $this->config->get('sms.default', 'log');

            return $driver;
        }
    }

    /**
     * Clear all cached driver instances so fresh drivers are resolved.
     */
    public function forgetDrivers(): self
    {
        $this->drivers = [];

        return $this;
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
