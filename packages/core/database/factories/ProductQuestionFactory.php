<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductQuestion;
use Reyhan\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductQuestion>
 */
class ProductQuestionFactory extends Factory
{
    protected $model = ProductQuestion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'question' => fake()->sentence().'؟',
            'is_approved' => true,
            'likes_count' => 0,
        ];
    }
}
