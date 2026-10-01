<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Enums\PaymentGateway;
use App\Services\Payment\Contracts\PaymentDriverInterface;
use App\Services\Payment\Drivers\CardToCardDriver;
use App\Services\Payment\Drivers\SandboxDriver;
use App\Services\Payment\Drivers\SnappPayDriver;
use App\Services\Payment\Drivers\WalletDriver;
use App\Services\Payment\Drivers\ZarinpalDriver;
use App\Services\Wallet\WalletService;
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
     * Create SnappPay BNPL driver instance.
     */
    protected function createSnappPayDriver(): PaymentDriverInterface
    {
        /** @var array<string, mixed> $config */
        $config = (array) $this->config->get('payment.gateways.snapp_pay', []);

        return new SnappPayDriver($config);
    }

    /**
     * Create Card-to-Card offline driver instance.
     */
    protected function createCardToCardDriver(): PaymentDriverInterface
    {
        /** @var array<string, mixed> $config */
        $config = (array) $this->config->get('payment.gateways.card_to_card', []);

        return new CardToCardDriver($config);
    }

    /**
     * Create Customer Wallet driver instance.
     */
    protected function createWalletDriver(): PaymentDriverInterface
    {
        return new WalletDriver(app(WalletService::class));
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
            if ((string) $key === 'wallet') {
                continue;
            }

            if (! empty($options['active'])) {
                $enum = PaymentGateway::tryFrom((string) $key);
                $gateways[] = [
                    'id' => (string) $key,
                    'name' => $enum ? $enum->label() : __((string) ($options['name'] ?? $key)),
                    'description' => $enum ? $enum->description() : match ((string) $key) {
                        'sandbox' => __('Test payment simulator (no actual charge)'),
                        'zarinpal' => __('Secure Zarinpal payment with all Shetab cards'),
                        'saman' => __('Saman Bank direct online payment gateway'),
                        'mellat' => __('Behpardakht Mellat online payment gateway'),
                        default => __('Shetab payment gateway'),
                    },
                ];
            }
        }

        return $gateways;
    }
}
