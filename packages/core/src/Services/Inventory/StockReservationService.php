<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Inventory;

use Reyhan\Core\Models\ProductVariant;
use Illuminate\Support\Facades\Redis;

class StockReservationService
{
    public const int DEFAULT_TTL = 900; // 15 minutes in seconds

    /**
     * Get available stock taking into account currently active reserved items in Redis.
     */
    public function getAvailableStock(ProductVariant $variant): int
    {
        $key = $this->variantReservationsKey($variant->id);
        $now = time();

        $lua = <<<'LUA'
            local key = KEYS[1]
            local now = tonumber(ARGV[1])

            -- Purge any expired reservations
            redis.call('ZREMRANGEBYSCORE', key, '-inf', now)

            local members = redis.call('ZRANGE', key, 0, -1)
            local totalReserved = 0

            for _, member in ipairs(members) do
                local _, qty = string.match(member, "^([^:]+):(%d+)$")
                if qty then
                    totalReserved = totalReserved + tonumber(qty)
                end
            end

            return totalReserved
        LUA;

        $reserved = (int) Redis::connection()->command('eval', [$lua, [$key, $now], 1]);

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

        $key = $this->variantReservationsKey($variantId);
        $now = time();
        $expireAt = $now + $ttlSeconds;
        $member = "{$reservationId}:{$quantity}";

        // Atomic Lua script to purge expired, check available stock, and add reservation
        $lua = <<<'LUA'
            local key = KEYS[1]
            local maxStock = tonumber(ARGV[1])
            local requestedQty = tonumber(ARGV[2])
            local now = tonumber(ARGV[3])
            local expireAt = tonumber(ARGV[4])
            local member = ARGV[5]

            -- 1. Purge expired reservations
            redis.call('ZREMRANGEBYSCORE', key, '-inf', now)

            -- 2. Calculate currently active reservations
            local members = redis.call('ZRANGE', key, 0, -1)
            local totalReserved = 0

            for _, m in ipairs(members) do
                local _, qty = string.match(m, "^([^:]+):(%d+)$")
                if qty then
                    totalReserved = totalReserved + tonumber(qty)
                end
            end

            local available = maxStock - totalReserved

            -- 3. Reserve if available
            if available >= requestedQty then
                redis.call('ZADD', key, expireAt, member)
                return 1
            else
                return 0
            end
        LUA;

        $result = Redis::connection()->command('eval', [$lua, [$key, $variant->stock, $quantity, $now, $expireAt, $member], 1]);

        return (bool) $result;
    }

    /**
     * Release a temporary reservation (e.g. on order cancellation, cart abandonment, or failure).
     */
    public function release(int $variantId, int $quantity, string $reservationId): void
    {
        $key = $this->variantReservationsKey($variantId);
        $member = "{$reservationId}:{$quantity}";

        $lua = <<<'LUA'
            local key = KEYS[1]
            local targetReservationId = ARGV[1]
            local explicitMember = ARGV[2]

            -- First try direct removal
            if redis.call('ZREM', key, explicitMember) == 0 then
                -- Fallback: find and remove any member with matching reservationId prefix
                local members = redis.call('ZRANGE', key, 0, -1)
                for _, m in ipairs(members) do
                    local resId, _ = string.match(m, "^([^:]+):(%d+)$")
                    if resId == targetReservationId then
                        redis.call('ZREM', key, m)
                    end
                end
            end

            return 1
        LUA;

        Redis::connection()->command('eval', [$lua, [$key, $reservationId, $member], 1]);
    }

    /**
     * Commit a reservation after successful payment (Tier 2).
     * Clears the Redis reservation item because DB stock is now permanently decremented.
     */
    public function commit(int $variantId, int $quantity, string $reservationId): void
    {
        $this->release($variantId, $quantity, $reservationId);
    }

    protected function variantReservationsKey(int $variantId): string
    {
        return "inventory:reservations:{$variantId}";
    }
}
