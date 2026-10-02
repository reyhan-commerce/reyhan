<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\LoyaltyTransaction;
use Reyhan\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyTransaction>
 */
class LoyaltyTransactionFactory extends Factory
{
    protected $model = LoyaltyTransaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'points' => 50,
            'type' => 'order_reward',
            'description' => 'امتیاز خرید از فروشگاه',
            'reference_id' => 'ORD-'.fake()->numerify('#####'),
        ];
    }

    public function earned(int $points = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => abs($points),
            'type' => 'order_reward',
            'description' => 'پاداش سفارش',
        ]);
    }

    public function spent(int $points = 50): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => -abs($points),
            'type' => 'coupon_redeem',
            'description' => 'تبدیل امتیاز به کوپن تخفیف',
        ]);
    }
}
