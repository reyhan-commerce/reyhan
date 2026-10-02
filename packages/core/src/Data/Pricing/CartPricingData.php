<?php

declare(strict_types=1);

namespace Reyhan\Core\Data\Pricing;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class CartPricingData extends Data
{
    /**
     * @param  array{code: string, title: string|null, type: string, value: int}|null  $appliedCoupon
     */
    public function __construct(
        #[MapName('original_items_subtotal')]
        public int $originalItemsSubtotal,

        #[MapName('items_subtotal')]
        public int $itemsSubtotal,

        #[MapName('catalog_discount')]
        public int $catalogDiscount,

        #[MapName('coupon_discount')]
        public int $couponDiscount,

        #[MapName('total_discount')]
        public int $totalDiscount,

        #[MapName('tax_amount')]
        public int $taxAmount,

        #[MapName('shipping_fee')]
        public int $shippingFee,

        #[MapName('is_free_shipping')]
        public bool $isFreeShipping,

        #[MapName('free_shipping_threshold')]
        public int $freeShippingThreshold,

        #[MapName('remaining_for_free_shipping')]
        public int $remainingForFreeShipping,

        #[MapName('free_shipping_progress')]
        public int $freeShippingProgress,

        #[MapName('final_payable')]
        public int $finalPayable,

        #[MapName('total_items_count')]
        public int $totalItemsCount,

        #[MapName('total_weight_grams')]
        public int $totalWeightGrams,

        #[MapName('applied_coupon')]
        public ?array $appliedCoupon = null,

        #[MapName('shipping_method_id')]
        public ?int $shippingMethodId = null,

        #[MapName('shipping_method_title')]
        public ?string $shippingMethodTitle = null,
    ) {}
}
