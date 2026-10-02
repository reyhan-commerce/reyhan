<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Cart;

use Closure;

final class CalculateTaxesPipe
{
    public function handle(CartCalculationContext $context, Closure $next): mixed
    {
        $subtotalAfterCoupon = max(0, $context->itemsSubtotal - $context->couponDiscount);

        // Pro-rata taxable base calculation after coupon discount
        $taxableSubtotalAfterCoupon = 0;
        if ($context->itemsSubtotal > 0 && $context->taxableItemsSubtotal > 0) {
            $discountRatio = min(1.0, $context->couponDiscount / $context->itemsSubtotal);
            $taxableSubtotalAfterCoupon = max(0, (int) round($context->taxableItemsSubtotal * (1 - $discountRatio)));
        }

        $taxRate = (int) config('reyhan.store.tax_rate_percent', 10);
        $taxMode = (string) config('reyhan.store.tax_mode', 'exclusive');

        if ($taxRate > 0 && $taxableSubtotalAfterCoupon > 0) {
            if ($taxMode === 'inclusive') {
                $context->taxAmount = (int) round($taxableSubtotalAfterCoupon * ($taxRate / (100 + $taxRate)));
                $context->finalPayable = $subtotalAfterCoupon + $context->shippingFee;
            } else {
                $context->taxAmount = (int) round($taxableSubtotalAfterCoupon * ($taxRate / 100));
                $context->finalPayable = $subtotalAfterCoupon + $context->taxAmount + $context->shippingFee;
            }
        } else {
            $context->taxAmount = 0;
            $context->finalPayable = $subtotalAfterCoupon + $context->shippingFee;
        }

        return $next($context);
    }
}
