<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Enums\CouponScope;
use Reyhan\Core\Enums\CouponType;
use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Carbon\Carbon;
use Reyhan\Core\Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property string $code
 * @property string $title
 * @property CouponType $type
 * @property int $value
 * @property int|null $min_order_amount
 * @property int|null $max_discount_amount
 * @property CouponScope $scope
 * @property int|null $usage_limit
 * @property int $used_count
 * @property int|null $usage_limit_per_user
 * @property Carbon|null $starts_at
 * @property Carbon|null $expires_at
 * @property bool $is_active
 * @property-read Collection<int, CouponUsage> $usages
 * @property-read Collection<int, Category> $categories
 * @property-read Collection<int, Brand> $brands
 * @property-read Collection<int, ProductVariant> $variants
 */
#[Guarded(['id'])]
class Coupon extends Model implements ProvidesActivityTitle
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logExcept(['created_at', 'updated_at', 'used_count'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function activityTitle(): ?string
    {
        return $this->code;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'scope' => CouponScope::class,
            'value' => 'integer',
            'min_order_amount' => 'integer',
            'max_discount_amount' => 'integer',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'usage_limit_per_user' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<CouponUsage, $this>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_categories');
    }

    /**
     * @return BelongsToMany<Brand, $this>
     */
    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'coupon_brands');
    }

    /**
     * @return BelongsToMany<ProductVariant, $this>
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'coupon_variants');
    }

    /**
     * @param  Builder<Coupon>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $q): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Determine if coupon is currently valid for given subtotal and optional user.
     */
    public function isValidFor(int $subtotal, ?User $user = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        if ($this->min_order_amount !== null && $subtotal < $this->min_order_amount) {
            return false;
        }

        if ($user && $this->usage_limit_per_user) {
            $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
            if ($userUsageCount >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate raw discount amount in Rial for an eligible subtotal.
     */
    public function calculateDiscountAmount(int $eligibleSubtotal): int
    {
        if ($this->type === CouponType::FreeShipping) {
            return 0; // Shipping discount is handled in ShippingService
        }

        if ($this->type === CouponType::Fixed) {
            $discount = min($this->value, $eligibleSubtotal);
        } else {
            // Percentage
            $discount = (int) round(($eligibleSubtotal * $this->value) / 100);
        }

        if ($this->max_discount_amount !== null && $this->max_discount_amount > 0) {
            $discount = min($discount, $this->max_discount_amount);
        }

        return $discount;
    }
}
