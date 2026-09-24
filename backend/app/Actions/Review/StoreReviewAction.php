<?php

declare(strict_types=1);

namespace App\Actions\Review;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

final class StoreReviewAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, Product $product, array $data): Review
    {
        $criteriaRatings = is_array($data['criteria_ratings'] ?? null) ? $data['criteria_ratings'] : [];
        if ($criteriaRatings === []) {
            $fallback = [];
            if (isset($data['longevity_rating'])) {
                $fallback['longevity'] = (int) $data['longevity_rating'];
            }
            if (isset($data['coverage_rating'])) {
                $fallback['coverage'] = (int) $data['coverage_rating'];
            }
            if (isset($data['value_rating'])) {
                $fallback['value'] = (int) $data['value_rating'];
            }
            $criteriaRatings = $fallback;
        }

        // Check if user has purchased this product in a confirmed order
        $isVerifiedPurchase = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->whereIn('status', [
                        OrderStatus::Processing,
                        OrderStatus::Shipped,
                        OrderStatus::Delivered,
                    ]);
            })
            ->exists();

        $review = Review::updateOrCreate(
            [
                'user_id' => $user->id,
                'product_id' => $product->id,
            ],
            [
                'rating' => $data['rating'],
                'criteria_ratings' => $criteriaRatings,
                'longevity_rating' => $data['longevity_rating'] ?? ($criteriaRatings['longevity'] ?? 5),
                'coverage_rating' => $data['coverage_rating'] ?? ($criteriaRatings['coverage'] ?? 5),
                'value_rating' => $data['value_rating'] ?? ($criteriaRatings['value'] ?? 5),
                'comment' => $data['comment'],
                'strengths' => $data['strengths'] ?? [],
                'weaknesses' => $data['weaknesses'] ?? [],
                'is_verified_purchase' => $isVerifiedPurchase,
                'status' => ReviewStatus::Pending,
            ]
        );

        $review->load('user');

        return $review;
    }
}
