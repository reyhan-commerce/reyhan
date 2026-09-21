<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\WishlistResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * List current user's wishlist products.
     */
    public function index(Request $request): JsonResponse
    {
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
    public function toggle(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        // Check if product exists
        $product = Product::findOrFail($productId);

        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json([
                'success' => true,
                'in_wishlist' => false,
                'message' => 'محصول از لیست علاقه‌مندی‌ها حذف شد.',
            ]);
        }

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'success' => true,
            'in_wishlist' => true,
            'message' => 'محصول به لیست علاقه‌مندی‌ها اضافه شد.',
        ]);
    }
}
