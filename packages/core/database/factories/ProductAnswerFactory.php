<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductAnswer;
use App\Models\ProductQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductAnswer>
 */
class ProductAnswerFactory extends Factory
{
    protected $model = ProductAnswer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_question_id' => ProductQuestion::factory(),
            'user_id' => User::factory(),
            'answer' => fake()->paragraph(),
            'is_approved' => true,
            'is_staff' => false,
            'likes_count' => 0,
        ];
    }
}
