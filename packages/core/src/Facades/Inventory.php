<?php

declare(strict_types=1);

namespace Reyhan\Core\Facades;

use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Services\Inventory\StockReservationService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static int getAvailableStock(ProductVariant $variant)
 * @method static bool reserve(int $variantId, int $quantity, string $reservationId, int $ttlSeconds = 900)
 * @method static void release(int $variantId, int $quantity, string $reservationId)
 * @method static void commit(int $variantId, int $quantity, string $reservationId)
 *
 * @see \Reyhan\Core\Services\Inventory\StockReservationService
 */
final class Inventory extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return StockReservationService::class;
    }
}
