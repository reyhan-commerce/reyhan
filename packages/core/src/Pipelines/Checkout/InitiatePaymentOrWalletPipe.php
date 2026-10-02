<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Checkout;

use Reyhan\Core\Actions\Accounting\CreateLedgerJournalEntryAction;
use Reyhan\Core\Data\Checkout\CreateOrderResultData;
use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Enums\PaymentStatus;
use Reyhan\Core\Events\Orders\OrderPaid;
use Reyhan\Core\Models\CouponUsage;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Notifications\Orders\OrderPaidNotification;
use Reyhan\Core\Services\Cart\CartService;
use Reyhan\Core\Services\Inventory\StockReservationService;
use Reyhan\Core\Services\Marketing\ReferralService;
use Reyhan\Core\Services\Payment\PaymentManager;
use Reyhan\Core\Services\Wallet\WalletService;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class InitiatePaymentOrWalletPipe
{
    public function __construct(
        protected PaymentManager $paymentManager,
        protected StockReservationService $stockReservationService,
        protected CartService $cartService,
        protected WalletService $walletService,
    ) {}

    /**
     * @param  Closure(OrderCreationContext): mixed  $next
     */
    public function handle(OrderCreationContext $context, Closure $next): mixed
    {
        $order = $context->order;
        $user = $context->user;
        $data = $context->data;
        $remainingPayable = $context->remainingPayable;
        $finalPayable = $context->finalPayable;
        $walletDeduction = $context->walletDeduction;
        $cart = $context->cart;

        // CASE A: 100% covered by Wallet
        if ($remainingPayable === 0) {
            $context->rollbackReservations($this->stockReservationService);

            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'gateway' => PaymentGateway::Wallet,
                'status' => PaymentStatus::Success,
                'amount' => $finalPayable,
                'authority' => 'WALLET-'.Str::random(20),
                'reference_id' => 'WLT-'.Str::random(12),
                'tracking_code' => Payment::generateTrackingCode(),
                'paid_at' => now(),
            ]);

            $context->payment = $payment;

            // Record balanced double-entry accounting journal transaction
            try {
                app(CreateLedgerJournalEntryAction::class)->recordOrderSettlement($order, $payment);
            } catch (Throwable $e) {
                Log::error("Failed to record ledger settlement for wallet order {$order->order_number}: {$e->getMessage()}");
            }

            if ($cart->coupon) {
                CouponUsage::create([
                    'coupon_id' => $cart->coupon->id,
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'discount_amount' => (int) $order->coupon_discount,
                ]);
                $cart->coupon->increment('used_count');
            }

            $this->cartService->clearCart($cart);

            app(ReferralService::class)->rewardReferralUponOrderCompletion($order);

            // Dispatch Order Notification outside DB transaction
            try {
                $recipient = $order->user ?? ($user->mobile ? Notification::route('sms', $user->mobile) : null);
                $recipient?->notify(new OrderPaidNotification($order, $payment->tracking_code));
            } catch (Throwable $e) {
                Log::warning("Failed to send wallet order notification for order {$order->order_number}: {$e->getMessage()}");
            }

            event(new OrderPaid($order, $payment));

            $separator = str_contains($data->callbackUrl, '?') ? '&' : '?';
            $redirectUrl = "{$data->callbackUrl}{$separator}Authority={$payment->authority}&Status=OK&payment_method=wallet";

            $context->result = new CreateOrderResultData(
                order: $order,
                payment: $payment,
                redirectUrl: $redirectUrl,
            );

            return $next($context);
        }

        // CASE B: Remaining amount paid via selected gateway OUTSIDE database transaction
        $driver = $this->paymentManager->driver($data->gateway->value);
        $payResult = $driver->request($order, $data->callbackUrl);

        if (! $payResult->success || ! $payResult->authority) {
            $context->rollbackReservations($this->stockReservationService);
            $order->update(['status' => OrderStatus::Cancelled]);

            if ($walletDeduction > 0) {
                $this->walletService->deposit(
                    user: $user,
                    amountRial: $walletDeduction,
                    description: "استرداد وجه کیف پول بابت لغو سفارش {$order->order_number}",
                    orderId: $order->id
                );
            }

            throw ValidationException::withMessages([
                'payment' => [$payResult->errorMessage ?? __('Error communicating with the payment gateway.')],
            ]);
        }

        // Record pending Payment in DB
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'gateway' => $data->gateway,
            'status' => PaymentStatus::Pending,
            'amount' => $remainingPayable,
            'authority' => $payResult->authority,
        ]);

        $context->payment = $payment;
        $context->result = new CreateOrderResultData(
            order: $order,
            payment: $payment,
            redirectUrl: (string) $payResult->redirectUrl,
        );

        return $next($context);
    }
}
