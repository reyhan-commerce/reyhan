<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Models\Address;
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

class CreateOrderAction
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
     *
     * @return array{order: Order, payment: Payment, redirect_url: string}
     */
    public function execute(
        User $user,
        int $addressId,
        string $shippingMethodValue,
        string $gatewayValue,
        string $callbackUrl,
        ?string $notes = null
    ): array {
        $address = Address::where('user_id', $user->id)
            ->with(['province', 'city'])
            ->findOrFail($addressId);

        $cart = $this->cartService->resolveCart($user);
        $cart->load(['items.productVariant.product', 'coupon']);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => ['سبد خرید شما خالی است.'],
            ]);
        }

        $shippingMethod = ShippingMethod::tryFrom($shippingMethodValue) ?? ShippingMethod::Pishtaz;
        $gateway = PaymentGateway::tryFrom($gatewayValue) ?? PaymentGateway::Sandbox;

        // Calculate Pricing
        $pricing = $this->pricingService->calculateCart($cart, $address->city);
        $shippingFee = $pricing['shipping_fee'];
        $finalPayable = $pricing['final_payable'];

        // Reserve Stock in Redis (Tier 1 Concurrency Locking)
        $reservationId = 'order_res_' . Str::random(16);
        $reservedVariantIds = [];

        foreach ($cart->items as $item) {
            $variant = $item->productVariant;

            if (! $variant || ! $variant->is_active) {
                $this->rollbackReservations($reservedVariantIds, $reservationId);
                throw ValidationException::withMessages([
                    'stock' => ["محصول «{$item->productVariant?->product?->name}» در حال حاضر فعال نیست."],
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
                    'stock' => ["موجودی تنوع «{$variant->title}» کافی نیست."],
                ]);
            }

            $reservedVariantIds[$variant->id] = $item->quantity;
        }

        // Create Order & OrderItems in DB Transaction
        return DB::transaction(function () use (
            $user,
            $address,
            $cart,
            $shippingMethod,
            $gateway,
            $pricing,
            $shippingFee,
            $finalPayable,
            $notes,
            $callbackUrl
        ) {
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
                'items_subtotal' => $pricing['items_subtotal'],
                'discount_amount' => $pricing['catalog_discount'],
                'coupon_discount' => $pricing['coupon_discount'],
                'coupon_code' => $pricing['applied_coupon']['code'] ?? null,
                'shipping_fee' => $shippingFee,
                'final_payable' => $finalPayable,
                'notes' => $notes,
            ]);

            // Save immutable item snapshots
            foreach ($cart->items as $item) {
                $variant = $item->productVariant;
                $product = $variant->product;

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
                    'attributes_snapshot' => $variant->attributeValues->map(fn ($av) => [
                        'attribute' => $av->attribute?->name,
                        'value' => $av->value,
                        'label' => $av->label,
                    ])->toArray(),
                ]);
            }

            // Initiate payment via PaymentManager
            $driver = $this->paymentManager->driver($gateway->value);
            $payResult = $driver->request($order, $callbackUrl);

            if (! $payResult->success || ! $payResult->authority) {
                throw ValidationException::withMessages([
                    'payment' => [$payResult->errorMessage ?? 'خطا در ارتباط با درگاه پرداخت بانکی.'],
                ]);
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'gateway' => $gateway,
                'status' => PaymentStatus::Pending,
                'amount' => $finalPayable,
                'authority' => $payResult->authority,
            ]);

            return [
                'order' => $order,
                'payment' => $payment,
                'redirect_url' => (string) $payResult->redirectUrl,
            ];
        });
    }

    /**
     * Rollback partial Redis stock reservations if any reservation fails.
     *
     * @param array<int, int> $reservedVariantIds
     */
    protected function rollbackReservations(array $reservedVariantIds, string $reservationId): void
    {
        foreach ($reservedVariantIds as $variantId => $qty) {
            $this->stockReservationService->release($variantId, $qty, $reservationId);
        }
    }
}
