<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $product_variant_id
 * @property string $product_name
 * @property string $variant_title
 * @property string $sku
 * @property int $unit_price
 * @property int $discount_amount
 * @property int $final_price
 * @property int $quantity
 * @property int $total_price
 * @property array<string, mixed>|null $attributes_snapshot
 * @property-read Order|null $order
 * @property-read Product|null $product
 * @property-read ProductVariant|null $productVariant
 */
#[Guarded(['id'])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'discount_amount' => 'integer',
            'allocated_discount' => 'integer',
            'is_tax_exempt' => 'boolean',
            'tax_amount' => 'integer',
            'final_price' => 'integer',
            'quantity' => 'integer',
            'total_price' => 'integer',
            'attributes_snapshot' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
