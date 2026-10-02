<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Checkout;

use Closure;

final class ApplyDynamicPromotionsPipe
{
    /**
     * Extension pipe to allow dynamic promotions, customer group tier discounts, and loyalty perks.
     *
     * @param  Closure(OrderCreationContext): mixed  $next
     */
    public function handle(OrderCreationContext $context, Closure $next): mixed
    {
        // Custom promotion extensions can mutate $context->customData or prepare coupon conditions here.
        return $next($context);
    }
}
