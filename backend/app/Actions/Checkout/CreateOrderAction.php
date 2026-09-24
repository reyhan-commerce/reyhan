<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\Data\Checkout\CreateOrderData;
use App\Data\Checkout\CreateOrderResultData;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\AttributeValue;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Services\Cart\CartService;
use App\Services\Inventory\StockReservationService;
use App\Services\Payment\PaymentManager;
use App\Services\Pricing\PricingService;
use App\Services\Shipping\ShippingService;
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

        $shippingMethod = $data->shippingMethod;
        $gateway = $data->gateway;

        // Calculate Pricing using DTO
        $pricing = $this->pricingService->calculateCart($cart, $address->city);
        $shippingFee = $pricing->shippingFee;
        $finalPayable = $pricing->finalPayable;

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
            $shippingMethod,
            $pricing,
            $shippingFee,
            $finalPayable,
            $data
        ): Order {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'status' => OrderStatus::PendingPayment,
                'shipping_method' => $shippingMethod,
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
                'final_payable' => $finalPayable,
                'notes' => $data->notes,
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

            return $order;
        });

        // 2. Initiate payment via PaymentManager OUTSIDE the database transaction (Farshid Rule 5 / Red Flag 3)
        $driver = $this->paymentManager->driver($gateway->value);
        $payResult = $driver->request($order, $data->callbackUrl);

        if (! $payResult->success || ! $payResult->authority) {
            $this->rollbackReservations($reservedVariantIds, $reservationId);
            $order->update(['status' => OrderStatus::Cancelled]);

            throw ValidationException::withMessages([
                'payment' => [$payResult->errorMessage ?? __('Error communicating with the payment gateway.')],
            ]);
        }

        // 3. Record pending Payment in DB
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'gateway' => $gateway,
            'status' => PaymentStatus::Pending,
            'amount' => $finalPayable,
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
