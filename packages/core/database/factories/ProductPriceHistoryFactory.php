<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductPriceHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductPriceHistory>
 */
class ProductPriceHistoryFactory extends Factory
{
    protected $model = ProductPriceHistory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'price' => 350000,
            'recorded_at' => now(),
        ];
    }
}
