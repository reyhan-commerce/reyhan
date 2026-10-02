<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Enums\OrderReturnStatus;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\OrderReturn;
use Reyhan\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OrderReturn>
 */
class OrderReturnFactory extends Factory
{
    protected $model = OrderReturn::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'return_number' => 'RET-'.strtoupper(Str::random(8)),
            'status' => OrderReturnStatus::Pending,
            'reason' => 'نقص فنی یا مغایرت کالا',
            'description' => fake()->sentence(),
            'photos' => [],
            'refund_method' => 'wallet',
            'refund_amount' => 250000,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'admin_notes' => null,
        ];
    }
}
