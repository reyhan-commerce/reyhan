<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Payment\Drivers;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Services\Payment\Contracts\PaymentDriverInterface;
use Reyhan\Core\Services\Payment\DTOs\PaymentRequestResult;
use Reyhan\Core\Services\Payment\DTOs\PaymentVerifyResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SepDriver implements PaymentDriverInterface
{
    private const string TOKEN_URL = 'https://sep.shaparak.ir/onlinepg/onlinepg';

    private const string VERIFY_URL = 'https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/VerifyTransaction';

    /**
     * @param  array{terminal_id?: string, cell_number_field?: string}  $config
     */
    public function __construct(
        private readonly array $config = []
    ) {}

    public function request(Order $order, string $callbackUrl): PaymentRequestResult
    {
        $terminalId = (string) ($this->config['terminal_id'] ?? config('payment.gateways.saman.terminal_id', '10000000'));
        $amount = (int) $order->final_payable; // In Rial
        $resNum = (string) $order->order_number;

        try {
            $response = Http::timeout(10)->post(self::TOKEN_URL, [
                'action' => 'token',
                'TerminalId' => $terminalId,
                'Amount' => $amount,
                'ResNum' => $resNum,
                'RedirectUrl' => $callbackUrl,
                'CellNumber' => $order->user?->mobile,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['token'] ?? null;
                $status = (int) ($data['status'] ?? -1);

                if ($status === 1 && ! empty($token)) {
                    $redirectUrl = "https://sep.shaparak.ir/OnlinePG/SendToken?token={$token}";

                    return new PaymentRequestResult(
                        success: true,
                        authority: (string) $token,
                        redirectUrl: $redirectUrl,
                        rawResponse: (array) $data,
                    );
                }

                return new PaymentRequestResult(
                    success: false,
                    authority: null,
                    redirectUrl: null,
                    errorMessage: $data['errorDesc'] ?? 'خطا در ارتباط با درگاه پرداخت سامان (سپ).',
                    rawResponse: (array) $data,
                );
            }
        } catch (Throwable $e) {
            Log::error("SEP Payment Request Exception for order {$order->order_number}: {$e->getMessage()}");
        }

        return new PaymentRequestResult(
            success: false,
            authority: null,
            redirectUrl: null,
            errorMessage: 'عدم پاسخگویی درگاه پرداخت اینترنتی سامان.',
        );
    }

    public function verify(Payment $payment, array $payload): PaymentVerifyResult
    {
        $terminalId = (string) ($this->config['terminal_id'] ?? config('payment.gateways.saman.terminal_id', '10000000'));
        $token = $payload['token'] ?? $payload['Token'] ?? $payment->authority;
        $refNum = (string) ($payload['RefNum'] ?? $payload['ref_num'] ?? '');
        $status = (int) ($payload['Status'] ?? $payload['status'] ?? -1);

        if ($status !== 2 && empty($refNum)) {
            return new PaymentVerifyResult(
                success: false,
                referenceId: null,
                trackingCode: null,
                cardPan: null,
                errorMessage: 'تراکنش توسط کاربر لغو شده یا ناموفق بوده است.',
                rawResponse: $payload,
            );
        }

        try {
            $response = Http::timeout(10)->post(self::VERIFY_URL, [
                'RefNum' => $refNum,
                'TerminalNumber' => $terminalId,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $resultCode = (int) ($data['ResultCode'] ?? $data['Status'] ?? -1);

                if ($resultCode === 0 || $resultCode === 1) { // Success or Verified
                    $traceNo = (string) ($data['TransactionDetail']['TraceNo'] ?? $data['TraceNo'] ?? $refNum);
                    $maskedPan = (string) ($data['TransactionDetail']['MaskedPan'] ?? $data['MaskedPan'] ?? ($payload['SecurePan'] ?? null));

                    return new PaymentVerifyResult(
                        success: true,
                        referenceId: $refNum,
                        trackingCode: $traceNo,
                        cardPan: $maskedPan,
                        rawResponse: (array) $data,
                    );
                }

                return new PaymentVerifyResult(
                    success: false,
                    referenceId: $refNum,
                    trackingCode: null,
                    cardPan: null,
                    errorMessage: (string) ($data['ResultDescription'] ?? 'تراکنش توسط بانک سامان تایید نشد.'),
                    rawResponse: (array) $data,
                );
            }
        } catch (Throwable $e) {
            Log::error("SEP Payment Verify Exception for payment {$payment->id}: {$e->getMessage()}");
        }

        return new PaymentVerifyResult(
            success: false,
            referenceId: null,
            trackingCode: null,
            cardPan: null,
            errorMessage: 'خطا در تایید تراکنش درگاه سامان.',
            rawResponse: $payload,
        );
    }
}
