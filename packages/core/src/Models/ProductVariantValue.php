<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Database\Factories\ProductVariantValueFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Table(name: 'product_variant_values', incrementing: true, timestamps: false)]
class ProductVariantValue extends Pivot
{
    /** @use HasFactory<ProductVariantValueFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * @return BelongsTo<AttributeValue, $this>
     */
    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }
}
