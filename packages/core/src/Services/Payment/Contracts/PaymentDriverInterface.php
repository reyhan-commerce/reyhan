<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Payment\Contracts;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Services\Payment\DTOs\PaymentRequestResult;
use Reyhan\Core\Services\Payment\DTOs\PaymentVerifyResult;

interface PaymentDriverInterface
{
    /**
     * Request a payment authority token and redirect URL for the order.
     */
    public function request(Order $order, string $callbackUrl): PaymentRequestResult;

    /**
     * Verify payment transaction signature and settlement status.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verify(Payment $payment, array $payload): PaymentVerifyResult;
}
