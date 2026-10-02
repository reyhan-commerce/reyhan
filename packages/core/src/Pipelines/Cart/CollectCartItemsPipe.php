<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Cart;

use Closure;
use Reyhan\Core\Models\CartItem;

final class CollectCartItemsPipe
{
    public function handle(CartCalculationContext $context, Closure $next): mixed
    {
        $context->items = $context->cart->items()
            ->with(['variant.product.category', 'variant.product.brand'])
            ->get();

        foreach ($context->items as $item) {
            /** @var CartItem $item */
            $variant = $item->variant;
            if (! $variant) {
                continue;
            }

            $qty = $item->quantity;
            $unitPrice = $variant->price;
            $compareAt = $variant->compare_at_price ?? $unitPrice;
            $lineSubtotal = $unitPrice * $qty;

            $context->itemsSubtotal += $lineSubtotal;
            $context->originalItemsSubtotal += $compareAt * $qty;
            $context->totalWeightGrams += ($variant->weight ?? 0) * $qty;
            $context->totalItemsCount += $qty;

            $isExempt = (bool) ($variant->product?->is_tax_exempt ?? false);
            if (! $isExempt) {
                $context->taxableItemsSubtotal += $lineSubtotal;
            }
        }

        return $next($context);
    }
}
