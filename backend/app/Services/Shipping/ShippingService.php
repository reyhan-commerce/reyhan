<?php

declare(strict_types=1);

namespace App\Services\Shipping;

use App\Models\City;
use App\Settings\GeneralSettings;

class ShippingService
{
    /**
     * Default base shipping cost for Post Pishtaz in Rial (650,000 Rial = 65,000 Toman).
     */
    public const int DEFAULT_BASE_FEE = 650000;

    /**
     * Additional fee per 500 grams above 1000 grams.
     */
    public const int EXTRA_WEIGHT_FEE_PER_500G = 100000;

    public function __construct(
        protected GeneralSettings $settings,
    ) {}

    /**
     * Calculate shipping fee, free shipping progress, and remaining amount.
     *
     * @return array{
     *     shipping_fee: int,
     *     is_free: bool,
     *     free_shipping_threshold: int,
     *     remaining_for_free_shipping: int,
     *     progress_percent: int,
     *     method_title: string,
     * }
     */
    public function calculate(
        int $subtotalRial,
        int $totalWeightGrams = 0,
        ?City $destinationCity = null,
        bool $couponGrantsFreeShipping = false
    ): array {
        $threshold = $this->settings->free_shipping_threshold;

        // If coupon grants free shipping or subtotal meets threshold
        if ($couponGrantsFreeShipping || ($threshold > 0 && $subtotalRial >= $threshold)) {
            return [
                'shipping_fee' => 0,
                'is_free' => true,
                'free_shipping_threshold' => $threshold,
                'remaining_for_free_shipping' => 0,
                'progress_percent' => 100,
                'method_title' => 'ارسال رایگان اکسپرس',
            ];
        }

        // Calculate base + weight adjustments
        $fee = self::DEFAULT_BASE_FEE;
        if ($totalWeightGrams > 1000) {
            $extraHalfKilos = (int) ceil(($totalWeightGrams - 1000) / 500);
            $fee += $extraHalfKilos * self::EXTRA_WEIGHT_FEE_PER_500G;
        }

        $remaining = max(0, $threshold - $subtotalRial);
        $progress = $threshold > 0
            ? (int) min(100, round(($subtotalRial / $threshold) * 100))
            : 100;

        return [
            'shipping_fee' => $fee,
            'is_free' => false,
            'free_shipping_threshold' => $threshold,
            'remaining_for_free_shipping' => $remaining,
            'progress_percent' => $progress,
            'method_title' => 'پست پیشتاز سراسری',
        ];
    }
}
