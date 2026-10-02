<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Shipping;

use Reyhan\Core\Models\City;
use Reyhan\Core\Models\ShippingMethod;
use Reyhan\Core\Settings\GeneralSettings;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

class ShippingService
{
    /**
     * Default base shipping cost for Post Pishtaz in Rial (650,000 Rial = 65,000 Toman).
     */
    public const int DEFAULT_BASE_FEE = 650000;

    /**
     * Additional fee per 500 grams above 1000 grams for fallback.
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
     *     shipping_method_id: int|null,
     * }
     */
    public function calculate(
        int $subtotalRial,
        int $totalWeightGrams = 0,
        ?City $destinationCity = null,
        bool $couponGrantsFreeShipping = false,
        ShippingMethod|\Reyhan\Core\Enums\ShippingMethod|int|string|null $shippingMethod = null
    ): array {
        $threshold = $this->settings->free_shipping_threshold;

        $resolvedMethod = $this->resolveShippingMethod($shippingMethod, $destinationCity);

        if ($resolvedMethod) {
            $feeData = $resolvedMethod->calculateFee(
                subtotalRial: $subtotalRial,
                totalWeightGrams: $totalWeightGrams,
                couponGrantsFreeShipping: $couponGrantsFreeShipping,
                fallbackThreshold: $threshold
            );

            return [
                'shipping_fee' => $feeData['shipping_fee'],
                'is_free' => $feeData['is_free'],
                'free_shipping_threshold' => $feeData['free_shipping_threshold'],
                'remaining_for_free_shipping' => $feeData['remaining_for_free_shipping'],
                'progress_percent' => $feeData['progress_percent'],
                'method_title' => $resolvedMethod->name,
                'shipping_method_id' => $resolvedMethod->id,
            ];
        }

        // Fallback if no ShippingMethod records in database
        if ($couponGrantsFreeShipping || ($threshold > 0 && $subtotalRial >= $threshold)) {
            return [
                'shipping_fee' => 0,
                'is_free' => true,
                'free_shipping_threshold' => $threshold,
                'remaining_for_free_shipping' => 0,
                'progress_percent' => 100,
                'method_title' => __('Free Express Delivery'),
                'shipping_method_id' => null,
            ];
        }

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
            'method_title' => __('Nationwide Pishtaz Post'),
            'shipping_method_id' => null,
        ];
    }

    /**
     * Get all available shipping methods for a destination city with calculated costs.
     *
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     slug: string,
     *     description: string|null,
     *     icon: string|null,
     *     shipping_fee: int,
     *     is_free: bool,
     *     estimated_delivery_days: string|null,
     *     requires_time_slot: bool,
     *     time_slots: array<int, array{date: string, jalali_date: string, day_name: string, slots: array<string>}>,
     * }>
     */
    public function getAvailableMethods(
        int $subtotalRial,
        int $totalWeightGrams = 0,
        ?City $destinationCity = null,
        bool $couponGrantsFreeShipping = false
    ): array {
        $methods = ShippingMethod::query()
            ->active()
            ->get();

        $provinceId = $destinationCity?->province_id;
        $globalThreshold = $this->settings->free_shipping_threshold;
        $timeSlots = null;

        $results = [];

        foreach ($methods as $method) {
            if (! $method->isAvailableForProvince($provinceId)) {
                continue;
            }

            $feeData = $method->calculateFee(
                subtotalRial: $subtotalRial,
                totalWeightGrams: $totalWeightGrams,
                couponGrantsFreeShipping: $couponGrantsFreeShipping,
                fallbackThreshold: $globalThreshold
            );

            $methodSlots = [];
            if ($method->requires_time_slot) {
                if ($timeSlots === null) {
                    $timeSlots = $this->getAvailableDeliveryTimeSlots();
                }
                $methodSlots = $timeSlots;
            }

            $results[] = [
                'id' => $method->id,
                'name' => $method->name,
                'slug' => $method->slug,
                'description' => $method->description,
                'icon' => $method->icon,
                'shipping_fee' => $feeData['shipping_fee'],
                'is_free' => $feeData['is_free'],
                'estimated_delivery_days' => $method->estimated_delivery_days,
                'requires_time_slot' => $method->requires_time_slot,
                'time_slots' => $methodSlots,
            ];
        }

        return $results;
    }

    /**
     * Generate available delivery days and time slots for express delivery.
     *
     * @return array<int, array{
     *     date: string,
     *     jalali_date: string,
     *     day_name: string,
     *     slots: array<string>,
     * }>
     */
    public function getAvailableDeliveryTimeSlots(int $daysCount = 4): array
    {
        $slots = [];
        $currentDate = Carbon::now('Asia/Tehran');

        // If after 14:00, start from tomorrow
        if ($currentDate->hour >= 14) {
            $currentDate->addDay();
        }

        $added = 0;
        while ($added < $daysCount) {
            // In Iran, Friday (dayOfWeek == Carbon::FRIDAY) is holiday
            if ($currentDate->dayOfWeek !== Carbon::FRIDAY) {
                $jalali = Jalalian::fromCarbon($currentDate);

                $dayLabel = match (true) {
                    $currentDate->isToday() => 'امروز',
                    $currentDate->isTomorrow() => 'فردا',
                    default => $jalali->format('%A'),
                };

                $availableSlots = [
                    '09:00 - 13:00 (صبح)',
                    '16:00 - 20:00 (عصر)',
                ];

                // If today and already past 10:00, only evening slot is available
                if ($currentDate->isToday() && Carbon::now('Asia/Tehran')->hour >= 10) {
                    $availableSlots = ['16:00 - 20:00 (عصر)'];
                }

                $slots[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'jalali_date' => $jalali->format('d %B'),
                    'day_name' => $dayLabel,
                    'slots' => $availableSlots,
                ];

                $added++;
            }

            $currentDate->addDay();
        }

        return $slots;
    }

    /**
     * Resolve a ShippingMethod model instance from various input formats.
     */
    protected function resolveShippingMethod(
        ShippingMethod|\Reyhan\Core\Enums\ShippingMethod|int|string|null $method,
        ?City $destinationCity = null
    ): ?ShippingMethod {
        if ($method instanceof ShippingMethod) {
            return $method;
        }

        if ($method instanceof \Reyhan\Core\Enums\ShippingMethod) {
            $method = $method->value;
        }

        if (is_numeric($method)) {
            return ShippingMethod::query()->whereKey((int) $method)->first();
        }

        if (is_string($method) && ! empty($method)) {
            $found = ShippingMethod::query()->where('slug', $method)->first();
            if ($found) {
                return $found;
            }
        }

        // Default: pick first active method available for city's province
        $provinceId = $destinationCity?->province_id;
        $candidates = ShippingMethod::query()->active()->get();

        foreach ($candidates as $candidate) {
            if ($candidate->isAvailableForProvince($provinceId)) {
                return $candidate;
            }
        }

        return $candidates->first();
    }
}
