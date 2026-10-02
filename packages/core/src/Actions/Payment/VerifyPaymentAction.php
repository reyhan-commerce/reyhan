<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Payment;

use Reyhan\Core\Actions\Accounting\CreateLedgerJournalEntryAction;
use Reyhan\Core\Data\Payment\VerifyPaymentResultData;
use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Enums\PaymentStatus;
use Reyhan\Core\Models\Coupon;
use Reyhan\Core\Models\CouponUsage;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Notifications\Orders\OrderPaidNotification;
use Reyhan\Core\Services\Cart\CartService;
use Reyhan\Core\Services\Inventory\StockReservationService;
use Reyhan\Core\Services\Marketing\ReferralService;
use Reyhan\Core\Services\Payment\PaymentManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

final class VerifyPaymentAction
{
    public function __construct(
        protected PaymentManager $paymentManager,
        protected StockReservationService $stockReservationService,
        protected CartService $cartService,
        protected ReferralService $referralService,
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

        // Database updates inside transaction with pessimistic locking
        $order = DB::transaction(function () use ($payment, $verifyResult): ?Order {
            // Lock payment record for concurrency safety
            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::where('id', $payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayment->status === PaymentStatus::Success) {
                return $lockedPayment->order;
            }

            $order = $lockedPayment->order;
            if (! $order) {
                return null;
            }

            $order->loadMissing('items');

            // Tier 2 Pessimistic Database Concurrency Locking & Stock Settlement
            $reservationId = $order->reservation_id ?? "order_{$order->id}";

            foreach ($order->items as $item) {
                /** @var ProductVariant|null $variant */
                $variant = ProductVariant::where('id', $item->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if ($variant) {
                    $variant->decrement('stock', $item->quantity);
                    // Commit/Release Redis reservation (Tier 1 -> Tier 2 transition)
                    $this->stockReservationService->commit($variant->id, $item->quantity, $reservationId);
                }
            }

            // Update Payment record
            $lockedPayment->update([
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

            // Record balanced double-entry accounting journal transaction
            try {
                app(CreateLedgerJournalEntryAction::class)->recordOrderSettlement($order, $lockedPayment);
            } catch (Throwable $e) {
                Log::error("Failed to record ledger settlement for order {$order->order_number}: {$e->getMessage()}");
            }

            // Settle Coupon Usage if applied
            if (! empty($order->coupon_code)) {
                /** @var Coupon|null $coupon */
                $coupon = Coupon::where('code', $order->coupon_code)->lockForUpdate()->first();
                if ($coupon) {
                    CouponUsage::firstOrCreate(
                        [
                            'coupon_id' => $coupon->id,
                            'order_id' => $order->id,
                        ],
                        [
                            'user_id' => $order->user_id,
                            'discount_amount' => (int) $order->coupon_discount,
                        ]
                    );
                    $coupon->increment('used_count');
                }
            }

            // Settle Referral Reward if customer was referred
            $this->referralService->rewardReferralUponOrderCompletion($order);

            // Clear User Cart
            if ($order->user) {
                $userCart = $this->cartService->resolveCart($order->user);
                $this->cartService->clearCart($userCart);
            }

            return $order;
        });

        $order = $order ?? $payment->order;
        $orderNumber = $order ? $order->order_number : '';
        $userMobile = $order?->user?->mobile;

        // Dispatch Order Confirmation Notification OUTSIDE the DB transaction (Farshid Rule 5 / Red Flag 3)
        if ($order) {
            try {
                $recipient = $order->user ?? ($userMobile ? Notification::route('sms', $userMobile) : null);
                $recipient?->notify(new OrderPaidNotification($order, $verifyResult->trackingCode));
            } catch (Throwable $e) {
                Log::warning("Failed to send order notification for order {$orderNumber}: {$e->getMessage()}");
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
