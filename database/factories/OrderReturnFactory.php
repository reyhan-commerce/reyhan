<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderReturnStatus;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\User;
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
