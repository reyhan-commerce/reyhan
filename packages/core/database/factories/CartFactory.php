<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\Cart;
use Reyhan\Core\Models\CartItem;
use Reyhan\Core\Models\Coupon;
use Reyhan\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'session_id' => null,
            'coupon_id' => null,
        ];
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'session_id' => Str::random(40),
        ]);
    }

    public function withCoupon(?Coupon $coupon = null): static
    {
        return $this->state(fn (array $attributes) => [
            'coupon_id' => $coupon ? $coupon->id : Coupon::factory(),
        ]);
    }

    public function withItems(int $count = 2): static
    {
        return $this->has(
            CartItem::factory()->count($count),
            'items'
        );
    }
}
