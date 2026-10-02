<?php

declare(strict_types=1);

namespace Reyhan\Core\Facades;

use Reyhan\Core\Models\Cart as CartModel;
use Reyhan\Core\Models\CartItem;
use Reyhan\Core\Models\Coupon;
use Reyhan\Core\Models\User;
use Reyhan\Core\Services\Cart\CartService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static CartModel resolveCart(?User $user = null, ?string $sessionId = null)
 * @method static CartItem addItem(CartModel $cart, int $variantId, int $quantity = 1)
 * @method static CartItem|null updateQuantity(CartItem $item, int $quantity)
 * @method static void removeItem(CartItem $item)
 * @method static void clearCart(CartModel $cart)
 * @method static Coupon applyCoupon(CartModel $cart, string $code)
 * @method static void removeCoupon(CartModel $cart)
 * @method static CartModel syncGuestCart(User $user, string $sessionId)
 *
 * @see \Reyhan\Core\Services\Cart\CartService
 */
final class Cart extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CartService::class;
    }
}
