<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\OrderItem;
use Reyhan\Core\Models\OrderReturn;
use Reyhan\Core\Models\OrderReturnItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderReturnItem>
 */
class OrderReturnItemFactory extends Factory
{
    protected $model = OrderReturnItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_return_id' => OrderReturn::factory(),
            'order_item_id' => OrderItem::factory(),
            'quantity' => 1,
            'price' => 250000,
            'reason' => 'نقص فنی کالا',
        ];
    }
}
