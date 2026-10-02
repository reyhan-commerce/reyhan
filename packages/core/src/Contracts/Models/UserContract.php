<?php

declare(strict_types=1);

namespace Reyhan\Core\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Domain Contract for Reyhan Customer User Entity.
 */
interface UserContract
{
    /**
     * Relationship to user addresses.
     */
    public function addresses(): HasMany;

    /**
     * Relationship to default shipping address.
     */
    public function defaultAddress(): HasOne;

    /**
     * Relationship to user orders.
     */
    public function orders(): HasMany;

    /**
     * Relationship to user wishlist items.
     */
    public function wishlists(): HasMany;

    /**
     * Relationship to user wallet transactions.
     */
    public function walletTransactions(): HasMany;
}
