<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Review\StoreReviewAction;
use App\Data\Review\StoreReviewData;
use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Review\StoreReviewRequest;
use App\Http\Resources\V1\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class ReviewController extends Controller
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
    public function store(
        StoreReviewRequest $request,
        int $productId,
        StoreReviewAction $action
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $product = Product::findOrFail($productId);

        $review = $action->execute($user, $product, StoreReviewData::from($request->validated()));

        return response()->json([
            'success' => true,
            'message' => __('Your review has been submitted successfully.'),
            'data' => new ReviewResource($review),
        ], 201);
    }
}
