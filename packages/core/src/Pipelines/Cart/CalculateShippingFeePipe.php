<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Cart;

use Closure;
use Reyhan\Core\Services\Shipping\ShippingService;

final class CalculateShippingFeePipe
{
    public function __construct(
        private readonly ShippingService $shippingService,
    ) {}

    public function handle(CartCalculationContext $context, Closure $next): mixed
    {
        $shipping = $this->shippingService->calculate(
            subtotalRial: $context->itemsSubtotal,
            totalWeightGrams: $context->totalWeightGrams,
            destinationCity: $context->destinationCity,
            couponGrantsFreeShipping: $context->couponGrantsFreeShipping,
            shippingMethod: $context->shippingMethod,
        );

        $context->shippingFee = $shipping['shipping_fee'];
        $context->isFreeShipping = $shipping['is_free'];
        $context->freeShippingThreshold = $shipping['free_shipping_threshold'];
        $context->remainingForFreeShipping = $shipping['remaining_for_free_shipping'];
        $context->freeShippingProgress = $shipping['progress_percent'];
        $context->shippingMethodId = $shipping['shipping_method_id'];
        $context->shippingMethodTitle = $shipping['method_title'];

        return $next($context);
    }
}
