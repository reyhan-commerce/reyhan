<?php

declare(strict_types=1);

namespace Reyhan\Core\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Domain Contract for Reyhan Cart Entity.
 */
interface CartContract
{
    /**
     * Relationship to the user owning the cart.
     */
    public function user(): BelongsTo;

    /**
     * Relationship to cart line items.
     */
    public function items(): HasMany;

    /**
     * Relationship to applied coupon.
     */
    public function coupon(): BelongsTo;
}
