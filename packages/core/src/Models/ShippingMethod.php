<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Carbon\Carbon;
use Reyhan\Core\Database\Factories\ShippingMethodFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property int $base_cost
 * @property int $cost_per_kg
 * @property int|null $free_shipping_threshold
 * @property string|null $estimated_delivery_days
 * @property bool $requires_time_slot
 * @property array<int>|null $supported_provinces
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Order> $orders
 */
#[Guarded(['id'])]
class ShippingMethod extends Model
{
    /** @use HasFactory<ShippingMethodFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_cost' => 'integer',
            'cost_per_kg' => 'integer',
            'free_shipping_threshold' => 'integer',
            'requires_time_slot' => 'boolean',
            'supported_provinces' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @param  Builder<ShippingMethod>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }

    /**
     * Determine if shipping method is available for the given province ID.
     */
    public function isAvailableForProvince(?int $provinceId): bool
    {
        if (empty($this->supported_provinces)) {
            return true;
        }

        if ($provinceId === null) {
            return true;
        }

        return in_array($provinceId, $this->supported_provinces, true);
    }

    /**
     * Calculate shipping fee, free shipping progress, and remaining amount.
     *
     * @return array{
     *     shipping_fee: int,
     *     is_free: bool,
     *     free_shipping_threshold: int,
     *     remaining_for_free_shipping: int,
     *     progress_percent: int,
     * }
     */
    public function calculateFee(
        int $subtotalRial,
        int $totalWeightGrams = 0,
        bool $couponGrantsFreeShipping = false,
        ?int $fallbackThreshold = null
    ): array {
        $threshold = $this->free_shipping_threshold ?? $fallbackThreshold ?? 0;

        if ($couponGrantsFreeShipping || ($threshold > 0 && $subtotalRial >= $threshold)) {
            return [
                'shipping_fee' => 0,
                'is_free' => true,
                'free_shipping_threshold' => $threshold,
                'remaining_for_free_shipping' => 0,
                'progress_percent' => 100,
            ];
        }

        $fee = $this->base_cost;

        if ($this->cost_per_kg > 0 && $totalWeightGrams > 1000) {
            $extraKilos = (int) ceil(($totalWeightGrams - 1000) / 1000);
            $fee += $extraKilos * $this->cost_per_kg;
        }

        $remaining = max(0, $threshold - $subtotalRial);
        $progress = $threshold > 0
            ? (int) min(100, round(($subtotalRial / $threshold) * 100))
            : 100;

        return [
            'shipping_fee' => $fee,
            'is_free' => false,
            'free_shipping_threshold' => $threshold,
            'remaining_for_free_shipping' => $remaining,
            'progress_percent' => $progress,
        ];
    }
}
