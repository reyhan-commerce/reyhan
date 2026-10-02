<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\StockAlert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockAlert>
 */
class StockAlertFactory extends Factory
{
    protected $model = StockAlert::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'mobile' => '0912'.fake()->numerify('#######'),
            'status' => 'pending',
            'notified_at' => null,
        ];
    }
}
