<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Enums\CouponScope;
use Reyhan\Core\Enums\CouponType;
use Reyhan\Core\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'COUPON-'.fake()->unique()->bothify('??###'),
            'title' => 'تخفیف ویژه '.fake()->word(),
            'type' => CouponType::Percentage,
            'value' => fake()->numberBetween(5, 30),
            'min_order_amount' => null,
            'max_discount_amount' => null,
            'scope' => CouponScope::All,
            'usage_limit' => 100,
            'used_count' => 0,
            'usage_limit_per_user' => 1,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ];
    }

    public function fixed(int $amount = 50_000): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CouponType::Fixed,
            'value' => $amount,
            'max_discount_amount' => null,
        ]);
    }

    public function percentage(int $percent = 15): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CouponType::Percentage,
            'value' => $percent,
        ]);
    }

    public function withMaxDiscount(int $amount = 500_000): static
    {
        return $this->state(fn (array $attributes) => [
            'max_discount_amount' => $amount,
        ]);
    }

    public function freeShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CouponType::FreeShipping,
            'value' => 0,
            'max_discount_amount' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->subMonths(2),
            'expires_at' => now()->subDay(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
