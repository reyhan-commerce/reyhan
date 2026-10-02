<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Checkout;

use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Models\AttributeValue;
use Reyhan\Core\Models\CardTransferReceipt;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\OrderItem;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Services\Wallet\WalletService;
use Closure;
use Illuminate\Support\Facades\DB;

final class PersistOrderRecordPipe
{
    public function __construct(
        protected WalletService $walletService,
    ) {}

    /**
     * @param  Closure(OrderCreationContext): mixed  $next
     */
    public function handle(OrderCreationContext $context, Closure $next): mixed
    {
        $order = DB::transaction(function () use ($context): Order {
            $user = $context->user;
            $address = $context->address;
            $cart = $context->cart;
            $pricing = $context->pricing;
            $remainingPayable = $context->remainingPayable;
            $walletDeduction = $context->walletDeduction;
            $finalPayable = $context->finalPayable;
            $shippingFee = $context->shippingFee;
            $shippingMethodCode = $context->shippingMethodCode;
            $shippingMethodId = $context->shippingMethodId;
            $reservationId = $context->reservationId;
            $data = $context->data;

            $initialStatus = ($remainingPayable === 0) ? OrderStatus::Processing : OrderStatus::PendingPayment;
            $paidAt = ($remainingPayable === 0) ? now() : null;

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'reservation_id' => $reservationId,
                'user_id' => $user->id,
                'status' => $initialStatus,
                'shipping_method' => $shippingMethodCode,
                'shipping_method_id' => $shippingMethodId,
                'delivery_date' => $data->deliveryDate,
                'delivery_time_slot' => $data->deliveryTimeSlot,
                'shipping_address' => [
                    'recipient_name' => $address?->recipient_name,
                    'recipient_mobile' => $address?->recipient_mobile,
                    'province_id' => $address?->province_id,
                    'province_name' => $address?->province?->name,
                    'city_id' => $address?->city_id,
                    'city_name' => $address?->city?->name,
                    'postal_code' => $address?->postal_code,
                    'address_line' => $address?->address_line,
                    'building_number' => $address?->building_number,
                    'unit' => $address?->unit,
                    'full_address' => $address?->full_address,
                ],
                'items_subtotal' => $pricing->itemsSubtotal,
                'discount_amount' => $pricing->catalogDiscount,
                'coupon_discount' => $pricing->couponDiscount,
                'coupon_code' => $pricing->appliedCoupon['code'] ?? null,
                'shipping_fee' => $shippingFee,
                'tax_amount' => $pricing->taxAmount,
                'wallet_paid_amount' => $walletDeduction,
                'final_payable' => $finalPayable,
                'notes' => $data->notes,
                'is_corporate_invoice' => $data->isCorporateInvoice,
                'corporate_data' => $data->isCorporateInvoice ? $data->corporateData : null,
                'paid_at' => $paidAt,
            ]);

            // Save immutable item snapshots with line-item discount & VAT breakdown
            $couponDiscountTotal = (int) $pricing->couponDiscount;
            $itemsCount = $cart->items->count();
            $itemIndex = 0;
            $allocatedTotal = 0;
            $taxRate = (int) config('reyhan.store.tax_rate_percent', 10);
            $taxMode = (string) config('reyhan.store.tax_mode', 'exclusive');

            foreach ($cart->items as $item) {
                $itemIndex++;
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
                $catalogDiscountAmount = max(0, $unitPrice - $finalPrice);
                $lineSubtotal = $finalPrice * $item->quantity;

                // Line-level coupon allocation (Pro-Rata)
                $lineCouponDiscount = 0;
                if ($couponDiscountTotal > 0 && $pricing->itemsSubtotal > 0) {
                    if ($itemIndex === $itemsCount) {
                        $lineCouponDiscount = max(0, $couponDiscountTotal - $allocatedTotal);
                    } else {
                        $lineCouponDiscount = (int) round($couponDiscountTotal * ($lineSubtotal / $pricing->itemsSubtotal));
                        $allocatedTotal += $lineCouponDiscount;
                    }
                }

                $isTaxExempt = (bool) ($product->is_tax_exempt ?? false);
                $netTaxableLine = max(0, $lineSubtotal - $lineCouponDiscount);
                $lineTaxAmount = 0;

                if (! $isTaxExempt && $taxRate > 0 && $netTaxableLine > 0) {
                    if ($taxMode === 'inclusive') {
                        $lineTaxAmount = (int) round($netTaxableLine * ($taxRate / (100 + $taxRate)));
                    } else {
                        $lineTaxAmount = (int) round($netTaxableLine * ($taxRate / 100));
                    }
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'variant_title' => $variant->title,
                    'sku' => $variant->sku,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $catalogDiscountAmount,
                    'allocated_discount' => $lineCouponDiscount,
                    'is_tax_exempt' => $isTaxExempt,
                    'tax_amount' => $lineTaxAmount,
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

            // If 100% covered by wallet, finalize stock decrement immediately in DB
            if ($remainingPayable === 0) {
                foreach ($cart->items as $item) {
                    /** @var ProductVariant|null $variant */
                    $variant = ProductVariant::where('id', $item->product_variant_id)
                        ->lockForUpdate()
                        ->first();

                    if ($variant) {
                        $variant->decrement('stock', $item->quantity);
                    }
                }
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

        $context->order = $order;

        return $next($context);
    }
}
