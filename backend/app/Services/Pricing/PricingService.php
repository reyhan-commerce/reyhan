<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Enums\CouponScope;
use App\Enums\CouponType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\City;
use App\Models\Coupon;
use App\Services\Shipping\ShippingService;
use Illuminate\Database\Eloquent\Collection;

class PricingService
{
    public function __construct(
        protected ShippingService $shippingService,
    ) {}

    /**
     * Compute full pricing breakdown for a given Cart.
     *
     * @return array{
     *     original_items_subtotal: int,
     *     items_subtotal: int,
     *     catalog_discount: int,
     *     coupon_discount: int,
     *     total_discount: int,
     *     shipping_fee: int,
     *     is_free_shipping: bool,
     *     free_shipping_threshold: int,
     *     remaining_for_free_shipping: int,
     *     free_shipping_progress: int,
     *     final_payable: int,
     *     total_items_count: int,
     *     total_weight_grams: int,
     *     applied_coupon: array{code: string, title: string|null, type: string, value: int}|null,
     * }
     */
    public function calculateCart(Cart $cart, ?City $destinationCity = null): array
    {
        $items = $cart->items()->with(['variant.product.category', 'variant.product.brand'])->get();

        $originalItemsSubtotal = 0;
        $itemsSubtotal = 0;
        $totalWeight = 0;
        $totalItemsCount = 0;

        foreach ($items as $item) {
            /** @var CartItem $item */
            $variant = $item->variant;
            if (! $variant) {
                continue;
            }

            $qty = $item->quantity;
            $unitPrice = $variant->price;
            $compareAt = $variant->compare_at_price ?? $unitPrice;

            $itemsSubtotal += $unitPrice * $qty;
            $originalItemsSubtotal += $compareAt * $qty;
            $totalWeight += ($variant->weight ?? 0) * $qty;
            $totalItemsCount += $qty;
        }

        $catalogDiscount = max(0, $originalItemsSubtotal - $itemsSubtotal);

        // Evaluate Coupon
        $coupon = $cart->coupon;
        $couponDiscount = 0;
        $couponGrantsFreeShipping = false;
        $appliedCouponData = null;

        if ($coupon && $coupon->isValidFor($itemsSubtotal, $cart->user)) {
            $appliedCouponData = [
                'code' => $coupon->code,
                'title' => $coupon->title,
                'type' => $coupon->type->value,
                'value' => $coupon->value,
            ];

            if ($coupon->type === CouponType::FreeShipping) {
                $couponGrantsFreeShipping = true;
            } else {
                // Calculate eligible subtotal based on coupon scope
                $eligibleSubtotal = $this->calculateEligibleSubtotal($items, $coupon);
                $couponDiscount = $coupon->calculateDiscountAmount($eligibleSubtotal);
            }
        }

        // Calculate Shipping
        $shipping = $this->shippingService->calculate(
            subtotalRial: $itemsSubtotal,
            totalWeightGrams: $totalWeight,
            destinationCity: $destinationCity,
            couponGrantsFreeShipping: $couponGrantsFreeShipping
        );

        $subtotalAfterCoupon = max(0, $itemsSubtotal - $couponDiscount);
        $finalPayable = $subtotalAfterCoupon + $shipping['shipping_fee'];
        $totalDiscount = $catalogDiscount + $couponDiscount;

        return [
            'original_items_subtotal' => $originalItemsSubtotal,
            'items_subtotal' => $itemsSubtotal,
            'catalog_discount' => $catalogDiscount,
            'coupon_discount' => $couponDiscount,
            'total_discount' => $totalDiscount,
            'shipping_fee' => $shipping['shipping_fee'],
            'is_free_shipping' => $shipping['is_free'],
            'free_shipping_threshold' => $shipping['free_shipping_threshold'],
            'remaining_for_free_shipping' => $shipping['remaining_for_free_shipping'],
            'free_shipping_progress' => $shipping['progress_percent'],
            'final_payable' => $finalPayable,
            'total_items_count' => $totalItemsCount,
            'total_weight_grams' => $totalWeight,
            'applied_coupon' => $appliedCouponData,
        ];
    }

    /**
     * Filter items matching the coupon scope to determine discount base amount.
     *
     * @param  Collection<int, CartItem>  $items
     */
    protected function calculateEligibleSubtotal($items, Coupon $coupon): int
    {
        if ($coupon->scope === CouponScope::All) {
            return (int) $items->sum(fn (CartItem $item) => $item->subtotal);
        }

        $eligibleSubtotal = 0;

        if ($coupon->scope === CouponScope::Categories) {
            $categoryIds = $coupon->categories()->pluck('categories.id')->all();
            foreach ($items as $item) {
                $catId = $item->variant?->product?->category_id;
                if ($catId && in_array($catId, $categoryIds, true)) {
                    $eligibleSubtotal += $item->subtotal;
                }
            }
        } elseif ($coupon->scope === CouponScope::Brands) {
            $brandIds = $coupon->brands()->pluck('brands.id')->all();
            foreach ($items as $item) {
                $brandId = $item->variant?->product?->brand_id;
                if ($brandId && in_array($brandId, $brandIds, true)) {
                    $eligibleSubtotal += $item->subtotal;
                }
            }
        } elseif ($coupon->scope === CouponScope::Variants) {
            $variantIds = $coupon->variants()->pluck('product_variants.id')->all();
            foreach ($items as $item) {
                if (in_array($item->product_variant_id, $variantIds, true)) {
                    $eligibleSubtotal += $item->subtotal;
                }
            }
        }

        return $eligibleSubtotal;
    }
}
