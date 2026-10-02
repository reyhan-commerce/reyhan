<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Payment\Drivers;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Services\Payment\Contracts\PaymentDriverInterface;
use Reyhan\Core\Services\Payment\DTOs\PaymentRequestResult;
use Reyhan\Core\Services\Payment\DTOs\PaymentVerifyResult;
use Illuminate\Support\Facades\Log;
use Throwable;

final class BehpardakhtDriver implements PaymentDriverInterface
{
    private const string REQUEST_URL = 'https://bpm.shaparak.ir/pgwchannel/services/pgw';

    private const string PAYMENT_URL = 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat';

    /**
     * @param  array{terminal_id?: string, user_name?: string, user_password?: string}  $config
     */
    public function __construct(
        private readonly array $config = []
    ) {}

    public function request(Order $order, string $callbackUrl): PaymentRequestResult
    {
        $terminalId = (string) ($this->config['terminal_id'] ?? config('payment.gateways.mellat.terminal_id', '1000000'));
        $userName = (string) ($this->config['user_name'] ?? config('payment.gateways.mellat.user_name', 'user'));
        $userPassword = (string) ($this->config['user_password'] ?? config('payment.gateways.mellat.user_password', 'pass'));
        $amount = (int) $order->final_payable; // In Rial
        $orderId = (int) $order->id;
        $localDate = date('Ymd');
        $localTime = date('His');

        try {
            // Simulated REST/SOAP proxy for Behpardakht Mellat
            $token = 'MEL_'.strtoupper(bin2hex(random_bytes(10)));
            $redirectUrl = self::PAYMENT_URL."?RefId={$token}";

            return new PaymentRequestResult(
                success: true,
                authority: $token,
                redirectUrl: $redirectUrl,
                rawResponse: ['res_code' => '0', 'ref_id' => $token],
            );
        } catch (Throwable $e) {
            Log::error("Behpardakht Request Exception for order {$order->order_number}: {$e->getMessage()}");
        }

        return new PaymentRequestResult(
            success: false,
            authority: null,
            redirectUrl: null,
            errorMessage: 'عدم پاسخگویی درگاه پرداخت به‌پرداخت ملت.',
        );
    }

    public function verify(Payment $payment, array $payload): PaymentVerifyResult
    {
        $resCode = (string) ($payload['ResCode'] ?? $payload['res_code'] ?? '0');
        $refId = (string) ($payload['RefId'] ?? $payload['ref_id'] ?? $payment->authority);
        $saleOrderId = (string) ($payload['SaleOrderId'] ?? $payload['sale_order_id'] ?? '');
        $saleReferenceId = (string) ($payload['SaleReferenceId'] ?? $payload['sale_reference_id'] ?? 'REF-'.rand(100000, 999999));
        $cardPan = (string) ($payload['CardHolderPan'] ?? $payload['card_pan'] ?? '');

        if ($resCode === '0' || $resCode === 'success') {
            return new PaymentVerifyResult(
                success: true,
                referenceId: $refId,
                trackingCode: $saleReferenceId,
                cardPan: $cardPan ?: null,
                rawResponse: $payload,
            );
        }

        return new PaymentVerifyResult(
            success: false,
            referenceId: $refId,
            trackingCode: null,
            cardPan: null,
            errorMessage: $this->translateResCode($resCode),
            rawResponse: $payload,
        );
    }

    private function translateResCode(string $code): string
    {
        return match ($code) {
            '11' => 'شماره کارت نامعتبر است.',
            '12' => 'موجودی حساب کافی نیست.',
            '13' => 'رمز وارد شده نادرست است.',
            '14' => 'تعداد دفعات ورود رمز غلط بیش از حد مجاز است.',
            '15' => 'کارت نامعتبر است.',
            '17' => 'کاربر از انجام تراکنش منصرف شده است.',
            '41' => 'شماره درخواست تکراری است.',
            default => 'تراکنش توسط بانک ملت تایید نشد.',
        };
    }
}
