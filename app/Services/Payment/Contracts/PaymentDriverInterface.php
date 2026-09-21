<?php

declare(strict_types=1);

namespace App\Services\Payment\Contracts;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\DTOs\PaymentRequestResult;
use App\Services\Payment\DTOs\PaymentVerifyResult;

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
