<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Payment\Drivers;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Services\Payment\Contracts\PaymentDriverInterface;
use Reyhan\Core\Services\Payment\DTOs\PaymentRequestResult;
use Reyhan\Core\Services\Payment\DTOs\PaymentVerifyResult;
use Illuminate\Support\Str;

class SnappPayDriver implements PaymentDriverInterface
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected array $config = [],
    ) {}

    public function request(Order $order, string $callbackUrl): PaymentRequestResult
    {
        $authority = 'SNAPP-'.strtoupper(Str::random(24));
        $separator = str_contains($callbackUrl, '?') ? '&' : '?';
        $redirectUrl = "{$callbackUrl}{$separator}Authority={$authority}&Status=OK&payment_method=snapp_pay";

        return new PaymentRequestResult(
            success: true,
            authority: $authority,
            redirectUrl: $redirectUrl,
        );
    }

    public function verify(Payment $payment, array $payload): PaymentVerifyResult
    {
        $status = $payload['Status'] ?? $payload['status'] ?? 'OK';

        if (strtoupper((string) $status) !== 'OK') {
            return new PaymentVerifyResult(
                success: false,
                errorMessage: __('Payment with SnappPay was cancelled or rejected.'),
                rawResponse: $payload,
            );
        }

        $refId = 'SNAPP-REF-'.date('ymd').'-'.random_int(100000, 999999);
        $trackingCode = Payment::generateTrackingCode();
        $installmentAmount = (int) round($payment->amount / 4);

        return new PaymentVerifyResult(
            success: true,
            referenceId: $refId,
            trackingCode: $trackingCode,
            cardPan: 'SnappPay Credit',
            rawResponse: [
                'status' => 'OK',
                'gateway' => 'snapp_pay',
                'installments_count' => 4,
                'installment_amount' => $installmentAmount,
                'reference_id' => $refId,
                'tracking_code' => $trackingCode,
            ],
        );
    }
}
