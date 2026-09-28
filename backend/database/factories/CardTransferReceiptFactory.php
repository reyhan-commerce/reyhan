<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CardTransferReceipt;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CardTransferReceipt>
 */
class CardTransferReceiptFactory extends Factory
{
    protected $model = CardTransferReceipt::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'payment_id' => null,
            'amount' => 500000,
            'tracking_number' => fake()->numerify('TRK########'),
            'source_card_number' => fake()->numerify('6037************'),
            'destination_card_number' => fake()->numerify('6104************'),
            'transferred_at' => now(),
            'receipt_path' => null,
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'admin_notes' => null,
        ];
    }
}
