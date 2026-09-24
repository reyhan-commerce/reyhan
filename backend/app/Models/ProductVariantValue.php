<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductVariantValueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductVariantValue extends Pivot
{
    /** @use HasFactory<ProductVariantValueFactory> */
    use HasFactory;

    protected $table = 'product_variant_values';

    public $incrementing = true;

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
