<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Checkout;

use Reyhan\Core\Events\Inventory\StockDepleted;
use Reyhan\Core\Events\Orders\OrderCreated;
use Closure;

final class FireOrderCreatedEventsPipe
{
    /**
     * @param  Closure(OrderCreationContext): mixed  $next
     */
    public function handle(OrderCreationContext $context, Closure $next): mixed
    {
        if ($context->order) {
            event(new OrderCreated($context->order));

            if ($context->remainingPayable === 0 && $context->cart) {
                foreach ($context->cart->items as $item) {
                    $variant = $item->productVariant?->fresh();
                    if ($variant && $variant->stock <= 0) {
                        event(new StockDepleted($variant));
                    }
                }
            }
        }

        return $next($context);
    }
}
