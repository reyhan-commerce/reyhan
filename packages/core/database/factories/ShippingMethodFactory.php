<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingMethod>
 */
class ShippingMethodFactory extends Factory
{
    protected $model = ShippingMethod::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'icon' => 'i-lucide-truck',
            'base_cost' => 650000,
            'cost_per_kg' => 100000,
            'free_shipping_threshold' => null,
            'estimated_delivery_days' => '۲ تا ۴ روز کاری',
            'requires_time_slot' => false,
            'supported_provinces' => null,
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
