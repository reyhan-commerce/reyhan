<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Payment\Drivers;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Services\Payment\Contracts\PaymentDriverInterface;
use Reyhan\Core\Services\Payment\DTOs\PaymentRequestResult;
use Reyhan\Core\Services\Payment\DTOs\PaymentVerifyResult;
use Illuminate\Support\Facades\Http;
use Throwable;

class ZarinpalDriver implements PaymentDriverInterface
{
    /**
     * @param array{
     *     merchant_id: string,
     *     sandbox: bool,
     *     mode: string,
     * } $config
     */
    public function __construct(
        protected array $config,
    ) {}

    public function request(Order $order, string $callbackUrl): PaymentRequestResult
    {
        $isSandbox = (bool) $this->config['sandbox'];
        $baseUrl = $isSandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment'
            : 'https://payment.zarinpal.com/pg/v4/payment';

        try {
            $payableRials = max(0, $order->final_payable - ($order->wallet_paid_amount ?? 0));

            $response = Http::timeout(10)->post("{$baseUrl}/request.json", [
                'merchant_id' => $this->config['merchant_id'],
                // Zarinpal v4 expects amount in Toman (order final_payable is in Rial)
                'amount' => (int) ($payableRials / 10),
                'description' => __('Payment for order #:order_number', ['order_number' => $order->order_number]),
                'callback_url' => $callbackUrl,
                'metadata' => [
                    'mobile' => $order->user?->mobile,
                    'email' => $order->user?->email,
                    'order_id' => $order->id,
                ],
            ]);

            $data = $response->json('data');

            if ($response->successful() && isset($data['code']) && $data['code'] === 100) {
                $authority = (string) $data['authority'];
                $startPayUrl = $isSandbox
                    ? "https://sandbox.zarinpal.com/pg/StartPay/{$authority}"
                    : "https://payment.zarinpal.com/pg/StartPay/{$authority}";

                return new PaymentRequestResult(
                    success: true,
                    authority: $authority,
                    redirectUrl: $startPayUrl,
                );
            }

            $errors = $response->json('errors') ?? [];
            $errorMsg = is_array($errors) ? ($errors['message'] ?? __('Error communicating with Zarinpal gateway.')) : __('Error communicating with Zarinpal gateway.');

            return new PaymentRequestResult(
                success: false,
                errorMessage: (string) $errorMsg,
            );
        } catch (Throwable $e) {
            return new PaymentRequestResult(
                success: false,
                errorMessage: __('System error communicating with Zarinpal: :error', ['error' => $e->getMessage()]),
            );
        }
    }

    public function verify(Payment $payment, array $payload): PaymentVerifyResult
    {
        $status = $payload['Status'] ?? $payload['status'] ?? null;

        if ($status !== 'OK') {
            return new PaymentVerifyResult(
                success: false,
                errorMessage: __('Transaction was cancelled by user or failed.'),
                rawResponse: $payload,
            );
        }

        $authority = $payload['Authority'] ?? $payment->authority;
        $isSandbox = (bool) $this->config['sandbox'];
        $baseUrl = $isSandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment'
            : 'https://payment.zarinpal.com/pg/v4/payment';

        try {
            $response = Http::timeout(10)->post("{$baseUrl}/verify.json", [
                'merchant_id' => $this->config['merchant_id'],
                // Amount in Toman
                'amount' => (int) ($payment->amount / 10),
                'authority' => $authority,
            ]);

            $data = $response->json('data');

            if ($response->successful() && isset($data['code']) && in_array($data['code'], [100, 101], true)) {
                $refId = (string) ($data['ref_id'] ?? '');
                $cardPan = (string) ($data['card_pan'] ?? '');

                return new PaymentVerifyResult(
                    success: true,
                    referenceId: $refId,
                    trackingCode: $refId,
                    cardPan: $cardPan,
                    rawResponse: (array) $data,
                );
            }

            $errors = $response->json('errors') ?? [];
            $msg = is_array($errors) ? ($errors['message'] ?? __('Invalid status code from Zarinpal.')) : __('Invalid status code from Zarinpal.');

            return new PaymentVerifyResult(
                success: false,
                errorMessage: (string) $msg,
                rawResponse: $response->json() ?? [],
            );
        } catch (Throwable $e) {
            return new PaymentVerifyResult(
                success: false,
                errorMessage: __('Error verifying transaction: :error', ['error' => $e->getMessage()]),
            );
        }
    }
}
