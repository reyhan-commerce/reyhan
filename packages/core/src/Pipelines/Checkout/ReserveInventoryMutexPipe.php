<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Checkout;

use Reyhan\Core\Services\Inventory\StockReservationService;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ReserveInventoryMutexPipe
{
    public function __construct(
        protected StockReservationService $stockReservationService,
    ) {}

    /**
     * @param  Closure(OrderCreationContext): mixed  $next
     */
    public function handle(OrderCreationContext $context, Closure $next): mixed
    {
        $context->reservationId = 'order_res_'.Str::random(16);
        $reservedVariantIds = [];

        foreach ($context->cart->items as $item) {
            $variant = $item->productVariant;

            $reserved = $this->stockReservationService->reserve(
                variantId: $variant->id,
                quantity: $item->quantity,
                reservationId: $context->reservationId,
                ttlSeconds: 900 // 15 minutes
            );

            if (! $reserved) {
                $context->reservedVariantIds = $reservedVariantIds;
                $context->rollbackReservations($this->stockReservationService);

                throw ValidationException::withMessages([
                    'stock' => [__('Insufficient stock for variant \':variant\'.', ['variant' => $variant->title])],
                ]);
            }

            $reservedVariantIds[$variant->id] = $item->quantity;
        }

        $context->reservedVariantIds = $reservedVariantIds;

        return $next($context);
    }
}
