<?php

declare(strict_types=1);

namespace App\Services\Payment\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\Contracts\PaymentDriverInterface;
use App\Services\Payment\DTOs\PaymentRequestResult;
use App\Services\Payment\DTOs\PaymentVerifyResult;
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
                errorMessage: 'پرداخت در درگاه شبیه‌ساز لغو یا ناموفق شد.',
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
