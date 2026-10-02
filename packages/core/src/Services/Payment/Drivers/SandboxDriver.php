<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Payment\Drivers;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Services\Payment\Contracts\PaymentDriverInterface;
use Reyhan\Core\Services\Payment\DTOs\PaymentRequestResult;
use Reyhan\Core\Services\Payment\DTOs\PaymentVerifyResult;
use Illuminate\Support\Str;

class SandboxDriver implements PaymentDriverInterface
{
    public function request(Order $order, string $callbackUrl): PaymentRequestResult
    {
        $authority = 'SB-'.strtoupper(Str::random(24));
        $separator = str_contains($callbackUrl, '?') ? '&' : '?';
        $redirectUrl = "{$callbackUrl}{$separator}Authority={$authority}&Status=OK";

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
                errorMessage: __('Payment was cancelled or failed in the sandbox gateway.'),
                rawResponse: $payload,
            );
        }

        $refId = 'REF-'.date('ymd').'-'.random_int(100000, 999999);
        $trackingCode = Payment::generateTrackingCode();
        $cardPan = '502229******'.random_int(1000, 9999);

        return new PaymentVerifyResult(
            success: true,
            referenceId: $refId,
            trackingCode: $trackingCode,
            cardPan: $cardPan,
            rawResponse: [
                'status' => 'OK',
                'reference_id' => $refId,
                'tracking_code' => $trackingCode,
                'card_pan' => $cardPan,
                'simulated' => true,
            ],
        );
    }
}
