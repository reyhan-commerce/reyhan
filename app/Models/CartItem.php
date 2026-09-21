<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'cart_id',
        'product_variant_id',
        'quantity',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

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
            get: fn (): int => (int) (($this->variant?->price ?? 0) * $this->quantity),
        );
    }

    /**
     * @return Attribute<int, never>
     */
    protected function originalSubtotal(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                $compare = $this->variant?->compare_at_price ?? $this->variant?->price ?? 0;

                return (int) ($compare * $this->quantity);
            },
        );
    }
}
