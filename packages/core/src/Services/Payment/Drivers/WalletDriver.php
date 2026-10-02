<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Payment\Drivers;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Services\Payment\Contracts\PaymentDriverInterface;
use Reyhan\Core\Services\Payment\DTOs\PaymentRequestResult;
use Reyhan\Core\Services\Payment\DTOs\PaymentVerifyResult;
use Reyhan\Core\Services\Wallet\WalletService;
use Illuminate\Support\Str;

class WalletDriver implements PaymentDriverInterface
{
    public function __construct(
        protected WalletService $walletService,
    ) {}

    public function request(Order $order, string $callbackUrl): PaymentRequestResult
    {
        $authority = 'WALLET-'.strtoupper(Str::random(24));
        $separator = str_contains($callbackUrl, '?') ? '&' : '?';
        $redirectUrl = "{$callbackUrl}{$separator}Authority={$authority}&Status=OK&payment_method=wallet";

        return new PaymentRequestResult(
            success: true,
            authority: $authority,
            redirectUrl: $redirectUrl,
        );
    }

    public function verify(Payment $payment, array $payload): PaymentVerifyResult
    {
        $user = $payment->user;
        $order = $payment->order;

        if (! $user) {
            return new PaymentVerifyResult(
                success: false,
                errorMessage: __('User account not found for wallet payment.'),
            );
        }

        try {
            $transaction = $this->walletService->withdraw(
                user: $user,
                amountRial: $payment->amount,
                description: "پرداخت سفارش شماره {$order?->order_number}",
                orderId: $order?->id,
                meta: ['payment_id' => $payment->id]
            );

            // Record wallet paid amount in order
            if ($order) {
                $order->update([
                    'wallet_paid_amount' => $order->wallet_paid_amount + $payment->amount,
                ]);
            }

            $refId = 'WLT-'.date('ymd').'-'.$transaction->id;
            $trackingCode = Payment::generateTrackingCode();

            return new PaymentVerifyResult(
                success: true,
                referenceId: $refId,
                trackingCode: $trackingCode,
                cardPan: 'کیف پول کاربری',
                rawResponse: [
                    'status' => 'OK',
                    'gateway' => 'wallet',
                    'transaction_id' => $transaction->id,
                    'balance_after' => $transaction->balance_after,
                    'reference_id' => $refId,
                    'tracking_code' => $trackingCode,
                ],
            );
        } catch (\Throwable $e) {
            return new PaymentVerifyResult(
                success: false,
                errorMessage: $e->getMessage(),
                rawResponse: ['error' => $e->getMessage()],
            );
        }
    }
}
