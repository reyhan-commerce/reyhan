<?php

declare(strict_types=1);

namespace Reyhan\Core\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Domain Contract for Reyhan ProductVariant Entity.
 */
interface ProductVariantContract
{
    /**
     * Relationship to parent product.
     */
    public function product(): BelongsTo;

    /**
     * Relationship to attribute values.
     */
    public function attributeValues(): BelongsToMany;

    /**
     * Relationship to cart items referencing this variant.
     */
    public function cartItems(): HasMany;

    /**
     * Relationship to order items referencing this variant.
     */
    public function orderItems(): HasMany;

    /**
     * Check if variant has sufficient stock for requested quantity.
     */
    public function hasStock(int $quantity = 1): bool;
}
