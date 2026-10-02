<?php

declare(strict_types=1);

namespace Reyhan\Core\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Domain Contract for Reyhan Order Entity.
 */
interface OrderContract
{
    /**
     * Generate unique human-readable order number (e.g., ORD-260921-A8F2).
     */
    public static function generateOrderNumber(): string;

    /**
     * Relationship to the ordering customer user.
     */
    public function user(): BelongsTo;

    /**
     * Relationship to the shipping method.
     */
    public function shippingMethod(): BelongsTo;

    /**
     * Relationship to the ordered line items.
     */
    public function items(): HasMany;

    /**
     * Relationship to the order payment records.
     */
    public function payments(): HasMany;

    /**
     * Relationship to the successful payment record.
     */
    public function successfulPayment(): HasOne;

    /**
     * Relationship to the card-to-card transfer receipt.
     */
    public function cardTransferReceipt(): HasOne;
}
