<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ReviewResource;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Get approved reviews and rating statistics for a product.
     */
    public function index(int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $reviews = Review::where('product_id', $product->id)
            ->where('status', ReviewStatus::Approved)
            ->with('user')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $product->getReviewStats(),
                'reviews' => ReviewResource::collection($reviews)->response()->getData(true),
            ],
        ]);
    }

    /**
     * Store or update a customer review for a product.
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'longevity_rating' => ['required', 'integer', 'between:1,5'],
            'coverage_rating' => ['required', 'integer', 'between:1,5'],
            'value_rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:3', 'max:2000'],
            'strengths' => ['nullable', 'array', 'max:5'],
            'strengths.*' => ['string', 'max:100'],
            'weaknesses' => ['nullable', 'array', 'max:5'],
            'weaknesses.*' => ['string', 'max:100'],
        ]);

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
                'rating' => $validated['rating'],
                'longevity_rating' => $validated['longevity_rating'],
                'coverage_rating' => $validated['coverage_rating'],
                'value_rating' => $validated['value_rating'],
                'comment' => $validated['comment'],
                'strengths' => $validated['strengths'] ?? [],
                'weaknesses' => $validated['weaknesses'] ?? [],
                'is_verified_purchase' => $isVerifiedPurchase,
                'status' => ReviewStatus::Approved,
            ]
        );

        $review->load('user');

        return response()->json([
            'success' => true,
            'message' => 'دیدگاه شما با موفقیت ثبت و منتشر گردید.',
            'data' => new ReviewResource($review),
        ], 201);
    }
}
