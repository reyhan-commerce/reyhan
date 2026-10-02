<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Cart;

use Closure;
use Reyhan\Core\Enums\CouponScope;
use Reyhan\Core\Enums\CouponType;
use Reyhan\Core\Models\CartItem;
use Reyhan\Core\Models\Coupon;

final class ApplyCouponsAndPromotionsPipe
{
    public function handle(CartCalculationContext $context, Closure $next): mixed
    {
        $coupon = $context->cart->coupon;

        if ($coupon && $coupon->isValidFor($context->itemsSubtotal, $context->cart->user)) {
            $context->appliedCouponData = [
                'code' => $coupon->code,
                'title' => $coupon->title,
                'type' => $coupon->type->value,
                'value' => $coupon->value,
            ];

            if ($coupon->type === CouponType::FreeShipping) {
                $context->couponGrantsFreeShipping = true;
            } else {
                $eligibleSubtotal = $this->calculateEligibleSubtotal($context->items, $coupon);
                $context->couponDiscount = $coupon->calculateDiscountAmount($eligibleSubtotal);
            }
        }

        $context->totalDiscount = $context->catalogDiscount + $context->couponDiscount;

        return $next($context);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, CartItem>  $items
     */
    private function calculateEligibleSubtotal($items, Coupon $coupon): int
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
