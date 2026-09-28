<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\Data\Checkout\CreateOrderData;
use App\Data\Checkout\CreateOrderResultData;
use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\AttributeValue;
use App\Models\CardTransferReceipt;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Services\Cart\CartService;
use App\Services\Inventory\StockReservationService;
use App\Services\Marketing\ReferralService;
use App\Services\Payment\PaymentManager;
use App\Services\Pricing\PricingService;
use App\Services\Shipping\ShippingService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateOrderAction
{
    public function __construct(
        protected CartService $cartService,
        protected PricingService $pricingService,
        protected ShippingService $shippingService,
        protected StockReservationService $stockReservationService,
        protected PaymentManager $paymentManager,
        protected WalletService $walletService,
    ) {}

    /**
     * Execute checkout and order creation.
     */
    public function execute(
        User $user,
        CreateOrderData $data,
    ): CreateOrderResultData {
        $address = Address::where('user_id', $user->id)
            ->with(['province', 'city'])
            ->findOrFail($data->addressId);

        $cart = $this->cartService->resolveCart($user);
        $cart->load(['items.productVariant.product', 'coupon']);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => [__('Your shopping cart is empty.')],
            ]);
        }

        $shippingMethodInput = $data->shippingMethodId ?? $data->shippingMethod ?? 'pishtaz';
        $gateway = $data->gateway;

        // Calculate Pricing using DTO
        $pricing = $this->pricingService->calculateCart($cart, $address->city, $shippingMethodInput);
        $shippingFee = $pricing->shippingFee;
        $finalPayable = $pricing->finalPayable;
        $shippingMethodId = $pricing->shippingMethodId;
        $shippingMethodCode = is_string($data->shippingMethod)
            ? $data->shippingMethod
            : ($data->shippingMethod?->value ?? ($pricing->shippingMethodTitle ? 'express' : 'pishtaz'));

        // Calculate Wallet Deduction
        $walletDeduction = 0;
        if ($data->useWallet) {
            $userBalance = $this->walletService->getBalance($user);
            $walletDeduction = min($finalPayable, $userBalance);
        }
        $remainingPayable = $finalPayable - $walletDeduction;

        // Reserve Stock in Redis (Tier 1 Concurrency Locking)
        $reservationId = 'order_res_'.Str::random(16);
        $reservedVariantIds = [];

        foreach ($cart->items as $item) {
            $variant = $item->productVariant;

            if (! $variant || ! $variant->is_active) {
                $this->rollbackReservations($reservedVariantIds, $reservationId);
                throw ValidationException::withMessages([
                    'stock' => [__('Product \':product\' is currently not active.', ['product' => $item->productVariant?->product?->name])],
                ]);
            }

            $reserved = $this->stockReservationService->reserve(
                variantId: $variant->id,
                quantity: $item->quantity,
                reservationId: $reservationId,
                ttlSeconds: 900 // 15 minutes
            );

            if (! $reserved) {
                $this->rollbackReservations($reservedVariantIds, $reservationId);
                throw ValidationException::withMessages([
                    'stock' => [__('Insufficient stock for variant \':variant\'.', ['variant' => $variant->title])],
                ]);
            }

            $reservedVariantIds[$variant->id] = $item->quantity;
        }

        // 1. Create Order & OrderItems in DB Transaction and commit immediately
        $order = DB::transaction(function () use (
            $user,
            $address,
            $cart,
            $shippingMethodCode,
            $shippingMethodId,
            $pricing,
            $shippingFee,
            $finalPayable,
            $walletDeduction,
            $remainingPayable,
            $data
        ): Order {
            $initialStatus = ($remainingPayable === 0) ? OrderStatus::Processing : OrderStatus::PendingPayment;
            $paidAt = ($remainingPayable === 0) ? now() : null;

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'status' => $initialStatus,
                'shipping_method' => $shippingMethodCode,
                'shipping_method_id' => $shippingMethodId,
                'delivery_date' => $data->deliveryDate,
                'delivery_time_slot' => $data->deliveryTimeSlot,
                'shipping_address' => [
                    'recipient_name' => $address->recipient_name,
                    'recipient_mobile' => $address->recipient_mobile,
                    'province_id' => $address->province_id,
                    'province_name' => $address->province?->name,
                    'city_id' => $address->city_id,
                    'city_name' => $address->city?->name,
                    'postal_code' => $address->postal_code,
                    'address_line' => $address->address_line,
                    'building_number' => $address->building_number,
                    'unit' => $address->unit,
                    'full_address' => $address->full_address,
                ],
                'items_subtotal' => $pricing->itemsSubtotal,
                'discount_amount' => $pricing->catalogDiscount,
                'coupon_discount' => $pricing->couponDiscount,
                'coupon_code' => $pricing->appliedCoupon['code'] ?? null,
                'shipping_fee' => $shippingFee,
                'wallet_paid_amount' => $walletDeduction,
                'final_payable' => $finalPayable,
                'notes' => $data->notes,
                'is_corporate_invoice' => $data->isCorporateInvoice,
                'corporate_data' => $data->isCorporateInvoice ? $data->corporateData : null,
                'paid_at' => $paidAt,
            ]);

            // Save immutable item snapshots
            foreach ($cart->items as $item) {
                $variant = $item->productVariant;
                if (! $variant) {
                    continue;
                }

                $product = $variant->product;
                if (! $product) {
                    continue;
                }

                $unitPrice = $variant->compare_at_price ?? $variant->price;
                $finalPrice = $variant->price;
                $discountAmount = max(0, $unitPrice - $finalPrice);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'variant_title' => $variant->title,
                    'sku' => $variant->sku,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discountAmount,
                    'final_price' => $finalPrice,
                    'quantity' => $item->quantity,
                    'total_price' => $finalPrice * $item->quantity,
                    'attributes_snapshot' => $variant->attributeValues->map(fn (AttributeValue $av): array => [
                        'attribute' => $av->attribute?->name,
                        'value' => $av->value,
                        'label' => $av->label,
                    ])->all(),
                ]);
            }

            // Deduct from wallet if requested
            if ($walletDeduction > 0) {
                $this->walletService->withdraw(
                    user: $user,
                    amountRial: $walletDeduction,
                    description: __('messages.wallet.order_deduction', ['order_number' => $order->order_number]),
                    orderId: $order->id,
                );
            }

            // Store Card-to-Card offline receipt if submitted
            if ($data->gateway === PaymentGateway::CardToCard && ! empty($data->cardTrackingNumber)) {
                CardTransferReceipt::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'amount' => $remainingPayable > 0 ? $remainingPayable : $finalPayable,
                    'tracking_number' => $data->cardTrackingNumber,
                    'source_card_number' => $data->cardSourceNumber,
                    'transferred_at' => now(),
                    'status' => 'pending',
                ]);
            }

            return $order;
        });

        // CASE A: 100% covered by Wallet
        if ($remainingPayable === 0) {
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

            $this->cartService->clearCart($cart);

            app(ReferralService::class)->rewardReferralUponOrderCompletion($order);

            $separator = str_contains($data->callbackUrl, '?') ? '&' : '?';
            $redirectUrl = "{$data->callbackUrl}{$separator}Authority={$payment->authority}&Status=OK&payment_method=wallet";

            return new CreateOrderResultData(
                order: $order,
                payment: $payment,
                redirectUrl: $redirectUrl,
            );
        }

        // CASE B: Remaining amount paid via selected gateway OUTSIDE database transaction
        $driver = $this->paymentManager->driver($gateway->value);
        $payResult = $driver->request($order, $data->callbackUrl);

        if (! $payResult->success || ! $payResult->authority) {
            $this->rollbackReservations($reservedVariantIds, $reservationId);
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
            'gateway' => $gateway,
            'status' => PaymentStatus::Pending,
            'amount' => $remainingPayable,
            'authority' => $payResult->authority,
        ]);

        return new CreateOrderResultData(
            order: $order,
            payment: $payment,
            redirectUrl: (string) $payResult->redirectUrl,
        );
    }

    /**
     * Rollback partial Redis stock reservations if any reservation fails.
     *
     * @param  array<int, int>  $reservedVariantIds
     */
    protected function rollbackReservations(array $reservedVariantIds, string $reservationId): void
    {
        foreach ($reservedVariantIds as $variantId => $qty) {
            $this->stockReservationService->release($variantId, $qty, $reservationId);
        }
    }
}
