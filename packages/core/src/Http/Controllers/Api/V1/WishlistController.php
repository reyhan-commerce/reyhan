<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Actions\Wishlist\ToggleWishlistAction;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Resources\V1\WishlistResource;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\User;
use Reyhan\Core\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WishlistController extends Controller
{
    /**
     * List current user's wishlist products.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $wishlists = Wishlist::where('user_id', $user->id)
            ->with([
                'product.media',
                'product.activeVariants.attributeValues',
                'product.brand',
                'product.category',
            ])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => WishlistResource::collection($wishlists)->response()->getData(true),
        ]);
    }

    /**
     * Get array of product IDs currently in user's wishlist (for instant UI state).
     */
    public function ids(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $ids = Wishlist::where('user_id', $user->id)
            ->pluck('product_id')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'ids' => $ids,
            ],
        ]);
    }

    /**
     * Toggle a product in/out of current user's wishlist.
     */
    public function toggle(
        Request $request,
        int $productId,
        ToggleWishlistAction $action
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $product = Product::findOrFail($productId);
        $result = $action->execute($user, $product);

        return response()->json([
            'success' => true,
            'in_wishlist' => $result->inWishlist,
            'message' => $result->message,
        ]);
    }
}
