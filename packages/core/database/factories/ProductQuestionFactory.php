<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\User;
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
