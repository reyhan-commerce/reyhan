<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockStatus;
use Carbon\Carbon;
use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute as CastAttribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

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
     * @param  Builder<ProductVariant>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<ProductVariant>  $query
     */
    public function scopeInStock(Builder $query): void
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
}
