<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->numberBetween(100_000, 2_000_000);

        return [
            'product_id' => Product::factory(),
            'sku' => 'SKU-'.fake()->unique()->numerify('#####'),
            'barcode' => fake()->ean13(),
            'price' => $price,
            'compare_at_price' => null,
            'stock' => fake()->numberBetween(5, 100),
            'low_stock_threshold' => 5,
            'batch_number' => 'BATCH-'.fake()->numerify('###'),
            'expiry_date' => now()->addYears(2),
            'weight' => fake()->numberBetween(50, 500),
            'is_active' => true,
            'order' => 1,
        ];
    }

    public function inStock(int $quantity = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $quantity,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    public function lowStock(int $quantity = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $quantity,
            'low_stock_threshold' => 5,
        ]);
    }

    public function discounted(int $discountPercent = 20): static
    {
        return $this->state(function (array $attributes) use ($discountPercent) {
            $price = $attributes['price'] ?? 100_000;
            $compareAt = (int) round($price * (1 + ($discountPercent / 100)));

            return [
                'compare_at_price' => $compareAt,
            ];
        });
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
