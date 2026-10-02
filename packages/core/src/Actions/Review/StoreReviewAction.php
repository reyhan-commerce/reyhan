<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Review;

use Reyhan\Core\Data\Review\StoreReviewData;
use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Enums\ReviewStatus;
use Reyhan\Core\Models\OrderItem;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\Review;
use Reyhan\Core\Models\User;

final class StoreReviewAction
{
    public function execute(User $user, Product $product, StoreReviewData $data): Review
    {
        $criteriaRatings = $data->criteriaRatings ?? [];
        if ($criteriaRatings === []) {
            $fallback = [];
            if ($data->longevityRating !== null) {
                $fallback['longevity'] = $data->longevityRating;
            }
            if ($data->coverageRating !== null) {
                $fallback['coverage'] = $data->coverageRating;
            }
            if ($data->valueRating !== null) {
                $fallback['value'] = $data->valueRating;
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
                'rating' => $data->rating,
                'criteria_ratings' => $criteriaRatings,
                'longevity_rating' => $data->longevityRating ?? ($criteriaRatings['longevity'] ?? 5),
                'coverage_rating' => $data->coverageRating ?? ($criteriaRatings['coverage'] ?? 5),
                'value_rating' => $data->valueRating ?? ($criteriaRatings['value'] ?? 5),
                'comment' => $data->comment,
                'strengths' => $data->strengths ?? [],
                'weaknesses' => $data->weaknesses ?? [],
                'is_verified_purchase' => $isVerifiedPurchase,
                'status' => ReviewStatus::Pending,
            ]
        );

        $review->load('user');

        return $review;
    }
}
