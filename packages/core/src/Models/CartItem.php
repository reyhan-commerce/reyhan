<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $cart_id
 * @property int $product_variant_id
 * @property int $quantity
 * @property-read Cart|null $cart
 * @property-read ProductVariant|null $variant
 * @property-read ProductVariant|null $productVariant
 * @property-read int $subtotal
 * @property-read int $original_subtotal
 */
#[Guarded(['id'])]
class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function productVariant(): BelongsTo
    {
        return $this->variant();
    }

    /**
     * @return Attribute<int, never>
     */
    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                $variant = $this->variant;
                $price = $variant ? $variant->price : 0;

                return (int) ($price * $this->quantity);
            },
        );
    }

    /**
     * @return Attribute<int, never>
     */
    protected function originalSubtotal(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                $variant = $this->variant;
                $compare = $variant ? ($variant->compare_at_price ?? $variant->price) : 0;

                return (int) ($compare * $this->quantity);
            },
        );
    }
}
