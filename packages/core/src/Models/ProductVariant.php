<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Contracts\Models\ProductVariantContract;
use Reyhan\Core\Enums\StockStatus;
use Reyhan\Core\Events\Catalog\ProductRestockedEvent;
use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Carbon\Carbon;
use Reyhan\Core\Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute as CastAttribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property int $product_id
 * @property string $title
 * @property string $sku
 * @property string|null $barcode
 * @property int $price
 * @property int|null $compare_at_price
 * @property int $stock
 * @property int $low_stock_threshold
 * @property int|null $weight
 * @property bool $is_active
 * @property Carbon|null $expiry_date
 * @property int $order
 * @property-read Product|null $product
 * @property-read Collection<int, AttributeValue> $attributeValues
 * @property-read StockStatus $stock_status
 * @property-read int $discount_percent
 */
#[Guarded(['id'])]
class ProductVariant extends Model implements ProductVariantContract, ProvidesActivityTitle
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function activityTitle(): ?string
    {
        return $this->title ?: $this->sku;
    }

    protected static function booted(): void
    {
        static::updated(function (self $variant): void {
            if ($variant->wasChanged('stock')) {
                $oldStock = (int) $variant->getOriginal('stock');
                $newStock = (int) $variant->stock;

                if ($oldStock <= 0 && $newStock > 0) {
                    ProductRestockedEvent::dispatch($variant, $oldStock, $newStock);
                }
            }
        });

        static::saved(function (self $variant): void {
            if ($variant->wasChanged('price') || $variant->wasRecentlyCreated) {
                ProductPriceHistory::create([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'price' => $variant->price,
                    'recorded_at' => now(),
                ]);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'stock' => 'integer',
            'low_stock_threshold' => 'integer',
            'weight' => 'integer',
            'is_active' => 'boolean',
            'expiry_date' => 'date',
            'order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsToMany<AttributeValue, $this>
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_values');
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'product_variant_id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_variant_id');
    }

    /**
     * Check if variant has sufficient stock for requested quantity.
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->is_active && $this->stock >= $quantity;
    }

    /**
     * @param  Builder<ProductVariant>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<ProductVariant>  $query
     */
    #[Scope]
    protected function inStock(Builder $query): void
    {
        $query->where('stock', '>', 0);
    }

    /**
     * @return CastAttribute<string, never>
     */
    protected function title(): CastAttribute
    {
        return CastAttribute::make(
            get: fn (): string => $this->attributeValues
                ->pluck('value')
                ->implode(' / '),
        );
    }

    /**
     * @return CastAttribute<bool, never>
     */
    protected function isLowStock(): CastAttribute
    {
        return CastAttribute::make(
            get: fn (): bool => $this->stock > 0 && $this->stock <= $this->low_stock_threshold,
        );
    }

    /**
     * @return CastAttribute<bool, never>
     */
    protected function hasDiscount(): CastAttribute
    {
        return CastAttribute::make(
            get: fn (): bool => $this->compare_at_price !== null && $this->compare_at_price > $this->price,
        );
    }

    /**
     * @return CastAttribute<StockStatus, never>
     */
    protected function stockStatus(): CastAttribute
    {
        return CastAttribute::make(
            get: function (): StockStatus {
                if ($this->stock <= 0) {
                    return StockStatus::OutOfStock;
                }
                if ($this->stock <= $this->low_stock_threshold) {
                    return StockStatus::LowStock;
                }

                return StockStatus::InStock;
            },
        );
    }

    /**
     * @return HasMany<StockAlert, $this>
     */
    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }
}
