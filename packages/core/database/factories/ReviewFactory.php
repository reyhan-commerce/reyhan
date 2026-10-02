<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Enums\ReviewStatus;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\Review;
use Reyhan\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'rating' => fake()->numberBetween(3, 5),
            'criteria_ratings' => [
                'quality' => 5,
                'packaging' => 4,
            ],
            'longevity_rating' => 4,
            'coverage_rating' => 5,
            'value_rating' => 4,
            'comment' => 'کیفیت این محصول واقعاً عالی بود و ماندگاری بالایی داشت.',
            'strengths' => ['کیفیت عالی', 'ماندگاری بالا'],
            'weaknesses' => ['قیمت کمی بالا'],
            'is_verified_purchase' => true,
            'status' => ReviewStatus::Approved,
            'admin_reply' => null,
            'admin_reply_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReviewStatus::Approved,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReviewStatus::Pending,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReviewStatus::Rejected,
        ]);
    }

    public function verifiedPurchase(bool $verified = true): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified_purchase' => $verified,
        ]);
    }
}
