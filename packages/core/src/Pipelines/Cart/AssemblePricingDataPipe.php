<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Cart;

use Closure;
use Reyhan\Core\Data\Pricing\CartPricingData;

final class AssemblePricingDataPipe
{
    public function handle(CartCalculationContext $context, Closure $next): mixed
    {
        $context->result = new CartPricingData(
            originalItemsSubtotal: $context->originalItemsSubtotal,
            itemsSubtotal: $context->itemsSubtotal,
            catalogDiscount: $context->catalogDiscount,
            couponDiscount: $context->couponDiscount,
            totalDiscount: $context->totalDiscount,
            taxAmount: $context->taxAmount,
            shippingFee: $context->shippingFee,
            isFreeShipping: $context->isFreeShipping,
            freeShippingThreshold: $context->freeShippingThreshold,
            remainingForFreeShipping: $context->remainingForFreeShipping,
            freeShippingProgress: $context->freeShippingProgress,
            finalPayable: $context->finalPayable,
            totalItemsCount: $context->totalItemsCount,
            totalWeightGrams: $context->totalWeightGrams,
            appliedCoupon: $context->appliedCouponData,
            shippingMethodId: $context->shippingMethodId,
            shippingMethodTitle: $context->shippingMethodTitle,
        );

        return $next($context);
    }
}
