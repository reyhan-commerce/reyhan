<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Shipping\Contracts;

use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\Cart;

interface ShippingDriverInterface
{
    /**
     * Calculate shipping rate based on cart items, weight, and destination address.
     */
    public function calculateFee(Cart $cart, ?Address $address = null): int;

    /**
     * Validate the carrier's tracking code format.
     */
    public function validateTrackingCode(string $trackingCode): bool;

    /**
     * Generate the public tracking web URL for customer status tracking.
     */
    public function getTrackingUrl(string $trackingCode): ?string;
}
