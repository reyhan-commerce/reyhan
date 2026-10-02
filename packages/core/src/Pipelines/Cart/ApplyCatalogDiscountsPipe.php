<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Cart;

use Closure;

final class ApplyCatalogDiscountsPipe
{
    public function handle(CartCalculationContext $context, Closure $next): mixed
    {
        $context->catalogDiscount = max(0, $context->originalItemsSubtotal - $context->itemsSubtotal);

        return $next($context);
    }
}
