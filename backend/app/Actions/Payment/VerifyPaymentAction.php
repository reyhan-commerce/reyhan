<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Services\Cart\CartService;
use App\Services\Inventory\StockReservationService;
use App\Services\Payment\PaymentManager;
use App\Services\Sms\SmsManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class VerifyPaymentAction
{
    public function __construct(
        protected PaymentManager $paymentManager,
        protected StockReservationService $stockReservationService,
        protected CartService $cartService,
        protected SmsManager $smsManager,
    ) {}

    /**
     * Verify payment and settle order inventory.
     *
     * @param  array<string, mixed>  $payload
     * @return array{
     *     success: bool,
     *     message: string,
     *     order_number?: string,
     *     tracking_code?: string,
     *     reference_id?: string,
     *     amount?: int,
     *     paid_at?: string,
     * }
     */
    public function execute(string $authority, array $payload): array
    {
        $payment = Payment::where('authority', $authority)
            ->with(['order.items', 'order.user'])
            ->first();

        if (! $payment) {
            return [
                'success' => false,
                'message' => 'تراکنش پرداخت با شناسه داده‌شده یافت نشد.',
            ];
        }

        // Idempotency: if already paid successfully
        if ($payment->status === PaymentStatus::Success) {
            return [
                'success' => true,
                'message' => 'این تراکنش قبلاً با موفقیت تایید و پرداخت شده است.',
                'order_number' => $payment->order->order_number,
                'tracking_code' => $payment->tracking_code ?? '',
                'reference_id' => $payment->reference_id ?? '',
                'amount' => $payment->amount,
                'paid_at' => $payment->paid_at?->toIso8601String() ?? now()->toIso8601String(),
            ];
        }

        $driver = $this->paymentManager->driver($payment->gateway->value);
        $verifyResult = $driver->verify($payment, $payload);

        if (! $verifyResult->success) {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'gateway_response' => $verifyResult->rawResponse,
            ]);

            return [
                'success' => false,
                'message' => $verifyResult->errorMessage ?? 'پرداخت توسط درگاه تایید نشد.',
            ];
        }

        return DB::transaction(function () use ($payment, $verifyResult) {
            $order = $payment->order;

            // Tier 2 Pessimistic Database Concurrency Locking
            foreach ($order->items as $item) {
                /** @var ProductVariant|null $variant */
                $variant = ProductVariant::where('id', $item->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if ($variant) {
                    $variant->decrement('stock', $item->quantity);
                }
            }

            // Update Payment record
            $payment->update([
                'status' => PaymentStatus::Success,
                'reference_id' => $verifyResult->referenceId,
                'tracking_code' => $verifyResult->trackingCode,
                'card_pan' => $verifyResult->cardPan,
                'gateway_response' => $verifyResult->rawResponse,
                'paid_at' => now(),
            ]);

            // Update Order record
            $order->update([
                'status' => OrderStatus::Processing,
                'paid_at' => now(),
            ]);

            // Clear User Cart
            if ($order->user) {
                $userCart = $this->cartService->resolveCart($order->user);
                $this->cartService->clearCart($userCart);
            }

            // Dispatch Order Confirmation SMS
            try {
                if ($order->user?->mobile) {
                    $this->smsManager->send(
                        $order->user->mobile,
                        "سفارش شما با شماره {$order->order_number} و کد پیگیری {$verifyResult->trackingCode} با موفقیت ثبت و پرداخت شد. سپاس از خرید شما از ایزیشاپ."
                    );
                }
            } catch (Throwable $e) {
                Log::warning("Failed to send order SMS to {$order->user?->mobile}: {$e->getMessage()}");
            }

            return [
                'success' => true,
                'message' => 'پرداخت با موفقیت انجام شد و سفارش شما در حال آماده‌سازی است.',
                'order_number' => $order->order_number,
                'tracking_code' => $verifyResult->trackingCode ?? '',
                'reference_id' => $verifyResult->referenceId ?? '',
                'amount' => $payment->amount,
                'paid_at' => now()->toIso8601String(),
            ];
        });
    }
}
