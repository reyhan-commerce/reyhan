<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Checkout;

use Closure;

final class ExecutePreOrderHooksPipe
{
    /**
     * Interception point for plugins, fraud detection, and pre-persistence business rules.
     *
     * @param  Closure(OrderCreationContext): mixed  $next
     */
    public function handle(OrderCreationContext $context, Closure $next): mixed
    {
        return $next($context);
    }
}
