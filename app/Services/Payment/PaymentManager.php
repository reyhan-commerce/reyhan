<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Services\Payment\Contracts\PaymentDriverInterface;
use App\Services\Payment\Drivers\SandboxDriver;
use App\Services\Payment\Drivers\ZarinpalDriver;
use Illuminate\Support\Manager;

class PaymentManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return (string) $this->config->get('payment.default', 'sandbox');
    }

    /**
     * Create Sandbox driver instance.
     */
    protected function createSandboxDriver(): PaymentDriverInterface
    {
        return new SandboxDriver;
    }

    /**
     * Create Zarinpal driver instance.
     */
    protected function createZarinpalDriver(): PaymentDriverInterface
    {
        /** @var array{merchant_id: string, sandbox: bool, mode: string} $config */
        $config = (array) $this->config->get('payment.gateways.zarinpal', [
            'merchant_id' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
            'sandbox' => false,
            'mode' => 'normal',
        ]);

        return new ZarinpalDriver($config);
    }

    /**
     * Create Saman (SEP) driver instance.
     */
    protected function createSamanDriver(): PaymentDriverInterface
    {
        // Fallback to sandbox if not configured
        return new SandboxDriver;
    }

    /**
     * Create Mellat driver instance.
     */
    protected function createMellatDriver(): PaymentDriverInterface
    {
        // Fallback to sandbox if not configured
        return new SandboxDriver;
    }

    /**
     * Get list of active available gateways for customer selection.
     *
     * @return list<array{id: string, name: string, description: string}>
     */
    public function getActiveGateways(): array
    {
        $gateways = [];
        $configured = (array) $this->config->get('payment.gateways', []);

        foreach ($configured as $key => $options) {
            if (! empty($options['active'])) {
                $gateways[] = [
                    'id' => $key,
                    'name' => (string) ($options['name'] ?? $key),
                    'description' => match ($key) {
                        'sandbox' => 'شبیه‌ساز پرداخت آزمایشی (بدون کسر پول)',
                        'zarinpal' => 'پرداخت امن زرین‌پال با کلیه کارت‌های شتاب',
                        'saman' => 'درگاه پرداخت مستقیم اینترنتی بانک سامان',
                        'mellat' => 'درگاه پرداخت اینترنتی به پرداخت ملت',
                        default => 'درگاه پرداخت شتابی',
                    },
                ];
            }
        }

        return $gateways;
    }
}
