<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Data\Payment\VerifyPaymentResultData;
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

final class VerifyPaymentAction
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
     */
    public function execute(string $authority, array $payload): VerifyPaymentResultData
    {
        $payment = Payment::where('authority', $authority)
            ->with(['order.items', 'order.user'])
            ->first();

        if (! $payment) {
            return new VerifyPaymentResultData(
                success: false,
                message: __('Payment transaction was not found with the given authority.'),
            );
        }

        // Idempotency: if already paid successfully
        if ($payment->status === PaymentStatus::Success) {
            return new VerifyPaymentResultData(
                success: true,
                message: __('This transaction has already been verified and paid.'),
                orderNumber: $payment->order ? $payment->order->order_number : '',
                trackingCode: $payment->tracking_code ?? '',
                referenceId: $payment->reference_id ?? '',
                amount: $payment->amount,
                paidAt: $payment->paid_at?->toIso8601String() ?? now()->toIso8601String(),
            );
        }

        $driver = $this->paymentManager->driver($payment->gateway->value);
        $verifyResult = $driver->verify($payment, $payload);

        if (! $verifyResult->success) {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'gateway_response' => $verifyResult->rawResponse,
            ]);

            return new VerifyPaymentResultData(
                success: false,
                message: $verifyResult->errorMessage ?? __('Payment was not approved by the gateway.'),
            );
        }

        // Database updates inside transaction
        DB::transaction(function () use ($payment, $verifyResult): void {
            $order = $payment->order;
            if (! $order) {
                return;
            }

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
        });

        $order = $payment->order;
        $orderNumber = $order ? $order->order_number : '';
        $userMobile = $order?->user?->mobile;

        // Dispatch Order Confirmation SMS OUTSIDE the DB transaction (Farshid Rule 5 / Red Flag 3)
        if ($userMobile && $orderNumber) {
            try {
                $this->smsManager->send(
                    $userMobile,
                    __('Your order :order_number with tracking code :tracking_code has been placed and paid successfully. Thank you for shopping with us.', [
                        'order_number' => $orderNumber,
                        'tracking_code' => $verifyResult->trackingCode,
                    ])
                );
            } catch (Throwable $e) {
                Log::warning("Failed to send order SMS to {$userMobile}: {$e->getMessage()}");
            }
        }

        return new VerifyPaymentResultData(
            success: true,
            message: __('Payment completed successfully and your order is being processed.'),
            orderNumber: $orderNumber,
            trackingCode: $verifyResult->trackingCode ?? '',
            referenceId: $verifyResult->referenceId ?? '',
            amount: $payment->amount,
            paidAt: now()->toIso8601String(),
        );
    }
}
