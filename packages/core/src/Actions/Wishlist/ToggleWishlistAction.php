<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Wishlist;

use Reyhan\Core\Data\Wishlist\WishlistToggleResultData;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\User;
use Reyhan\Core\Models\Wishlist;

final class ToggleWishlistAction
{
    public function execute(User $user, Product $product): WishlistToggleResultData
    {
        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return new WishlistToggleResultData(
                inWishlist: false,
                message: __('Product removed from wishlist.'),
            );
        }

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return new WishlistToggleResultData(
            inWishlist: true,
            message: __('Product added to wishlist.'),
        );
    }
}
