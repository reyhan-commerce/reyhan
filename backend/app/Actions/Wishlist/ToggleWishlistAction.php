<?php

declare(strict_types=1);

namespace App\Actions\Wishlist;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;

final class ToggleWishlistAction
{
    /**
     * @return array{in_wishlist: bool, message: string}
     */
    public function execute(User $user, Product $product): array
    {
        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return [
                'in_wishlist' => false,
                'message' => __('Product removed from wishlist.'),
            ];
        }

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return [
            'in_wishlist' => true,
            'message' => __('Product added to wishlist.'),
        ];
    }
}
