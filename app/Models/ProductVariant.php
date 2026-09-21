<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute as CastAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'price',
        'compare_at_price',
        'stock',
        'low_stock_threshold',
        'batch_number',
        'expiry_date',
        'weight',
        'is_active',
        'order',
    ];

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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_values');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
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
