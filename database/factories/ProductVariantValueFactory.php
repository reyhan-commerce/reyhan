<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AttributeValue;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariantValue>
 */
class ProductVariantValueFactory extends Factory
{
    protected $model = ProductVariantValue::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::factory(),
            'attribute_value_id' => AttributeValue::factory(),
        ];
    }
}
