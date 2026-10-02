<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Payment\Drivers;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Services\Payment\Contracts\PaymentDriverInterface;
use Reyhan\Core\Services\Payment\DTOs\PaymentRequestResult;
use Reyhan\Core\Services\Payment\DTOs\PaymentVerifyResult;
use Illuminate\Support\Str;

class CardToCardDriver implements PaymentDriverInterface
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected array $config = [],
    ) {}

    public function request(Order $order, string $callbackUrl): PaymentRequestResult
    {
        $authority = 'C2C-'.strtoupper(Str::random(24));
        $separator = str_contains($callbackUrl, '?') ? '&' : '?';
        $redirectUrl = "{$callbackUrl}{$separator}Authority={$authority}&Status=OK&payment_method=card_to_card";

        return new PaymentRequestResult(
            success: true,
            authority: $authority,
            redirectUrl: $redirectUrl,
        );
    }

    public function verify(Payment $payment, array $payload): PaymentVerifyResult
    {
        $refId = 'C2C-REF-'.date('ymd').'-'.random_int(100000, 999999);
        $trackingCode = Payment::generateTrackingCode();

        return new PaymentVerifyResult(
            success: true,
            referenceId: $refId,
            trackingCode: $trackingCode,
            cardPan: $payload['card_pan'] ?? 'کارت‌به‌کارت',
            rawResponse: [
                'status' => 'PENDING_ADMIN_REVIEW',
                'gateway' => 'card_to_card',
                'reference_id' => $refId,
                'tracking_code' => $trackingCode,
                'note' => 'پرداخت آفلاین کارت‌به‌کارت ثبت شد و در انتظار تایید فیش توسط امور مالی است.',
            ],
        );
    }
}
