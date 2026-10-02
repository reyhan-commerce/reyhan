<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Cart;

use Reyhan\Core\Data\Pricing\CartPricingData;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Models\City;
use Reyhan\Core\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Collection;

final class CartCalculationContext
{
    /**
     * @var Collection<int, \Reyhan\Core\Models\CartItem>
     */
    public Collection $items;

    public int $originalItemsSubtotal = 0;
    public int $itemsSubtotal = 0;
    public int $taxableItemsSubtotal = 0;
    public int $totalWeightGrams = 0;
    public int $totalItemsCount = 0;

    public int $catalogDiscount = 0;
    public int $couponDiscount = 0;
    public int $totalDiscount = 0;
    public bool $couponGrantsFreeShipping = false;
    /** @var array{code: string, title: string|null, type: string, value: int}|null */
    public ?array $appliedCouponData = null;

    public int $shippingFee = 0;
    public bool $isFreeShipping = false;
    public int $freeShippingThreshold = 0;
    public int $remainingForFreeShipping = 0;
    public int $freeShippingProgress = 0;
    public ?int $shippingMethodId = null;
    public ?string $shippingMethodTitle = null;

    public int $taxAmount = 0;
    public int $finalPayable = 0;

    /**
     * Dynamic attributes bag for plugins and custom calculations.
     *
     * @var array<string, mixed>
     */
    public array $attributes = [];

    public ?CartPricingData $result = null;

    public function __construct(
        public readonly Cart $cart,
        public readonly ?City $destinationCity = null,
        public readonly ShippingMethod|\Reyhan\Core\Enums\ShippingMethod|int|string|null $shippingMethod = null,
    ) {
        $this->items = new Collection();
    }
}
