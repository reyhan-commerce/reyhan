<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\ProductVariant;
use Illuminate\Support\Facades\Redis;

class StockReservationService
{
    public const int DEFAULT_TTL = 900; // 15 minutes in seconds

    /**
     * Get available stock taking into account currently reserved items in Redis.
     */
    public function getAvailableStock(ProductVariant $variant): int
    {
        $reserved = (int) Redis::get($this->variantReservedKey($variant->id));

        return max(0, $variant->stock - $reserved);
    }

    /**
     * Temporarily reserve variant stock in Redis (Tier 1 Concurrency Locking).
     */
    public function reserve(int $variantId, int $quantity, string $reservationId, int $ttlSeconds = self::DEFAULT_TTL): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $variant = ProductVariant::find($variantId);
        if (! $variant || ! $variant->is_active) {
            return false;
        }

        $reservedKey = $this->variantReservedKey($variantId);
        $reservationKey = $this->reservationItemKey($reservationId, $variantId);

        // Atomic Lua script to check availability and reserve
        $lua = <<<'LUA'
            local reservedKey = KEYS[1]
            local reservationKey = KEYS[2]
            local maxStock = tonumber(ARGV[1])
            local requestedQty = tonumber(ARGV[2])
            local ttl = tonumber(ARGV[3])

            local currentReserved = tonumber(redis.call('GET', reservedKey) or 0)
            local available = maxStock - currentReserved

            if available >= requestedQty then
                redis.call('INCRBY', reservedKey, requestedQty)
                redis.call('SETEX', reservationKey, ttl, requestedQty)
                return 1
            else
                return 0
            end
        LUA;

        $result = Redis::connection()->command('eval', [$lua, [$reservedKey, $reservationKey, $variant->stock, $quantity, $ttlSeconds], 2]);

        return (bool) $result;
    }

    /**
     * Release a temporary reservation (e.g. on order cancellation, cart abandonment, or failure).
     */
    public function release(int $variantId, int $quantity, string $reservationId): void
    {
        $reservedKey = $this->variantReservedKey($variantId);
        $reservationKey = $this->reservationItemKey($reservationId, $variantId);

        $lua = <<<'LUA'
            local reservedKey = KEYS[1]
            local reservationKey = KEYS[2]
            local qty = tonumber(ARGV[1])

            redis.call('DEL', reservationKey)
            local current = tonumber(redis.call('GET', reservedKey) or 0)
            if current > 0 then
                local newReserved = math.max(0, current - qty)
                redis.call('SET', reservedKey, newReserved)
            end
            return 1
        LUA;

        Redis::connection()->command('eval', [$lua, [$reservedKey, $reservationKey, $quantity], 2]);
    }

    /**
     * Commit a reservation after successful payment (Tier 2).
     * Clears the Redis reservation counter because DB stock is now decremented.
     */
    public function commit(int $variantId, int $quantity, string $reservationId): void
    {
        $this->release($variantId, $quantity, $reservationId);
    }

    protected function variantReservedKey(int $variantId): string
    {
        return "inventory:reserved:{$variantId}";
    }

    protected function reservationItemKey(string $reservationId, int $variantId): string
    {
        return "inventory:reservation:{$reservationId}:{$variantId}";
    }
}
