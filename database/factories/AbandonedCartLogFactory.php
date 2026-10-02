<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AbandonedCartLog;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AbandonedCartLog>
 */
class AbandonedCartLogFactory extends Factory
{
    protected $model = AbandonedCartLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'user_id' => User::factory(),
            'notified_at' => now(),
            'coupon_id' => null,
            'status' => 'sent',
        ];
    }
}
