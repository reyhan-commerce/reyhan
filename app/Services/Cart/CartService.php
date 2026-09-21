<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * Resolve or create the cart for an authenticated user or guest session.
     */
    public function resolveCart(?User $user = null, ?string $sessionId = null): Cart
    {
        if ($user) {
            /** @var Cart $cart */
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => $sessionId]
            );

            return $cart;
        }

        if (! $sessionId) {
            $sessionId = (string) Str::uuid();
        }

        /** @var Cart $cart */
        $cart = Cart::firstOrCreate(
            ['session_id' => $sessionId, 'user_id' => null]
        );

        return $cart;
    }

    /**
     * Add an item to the cart with inventory validation.
     */
    public function addItem(Cart $cart, int $variantId, int $quantity = 1): CartItem
    {
        $variant = ProductVariant::query()->active()->findOrFail($variantId);

        if ($variant->stock <= 0) {
            throw ValidationException::withMessages([
                'variant_id' => ['این مدل از محصول در حال حاضر در انبار موجود نیست.'],
            ]);
        }

        /** @var CartItem|null $existing */
        $existing = $cart->items()->where('product_variant_id', $variantId)->first();
        $targetQty = ($existing?->quantity ?? 0) + $quantity;

        // Cap at variant stock or max 10
        $maxAllowed = min($variant->stock, 10);
        if ($targetQty > $maxAllowed) {
            $targetQty = $maxAllowed;
        }

        if ($existing) {
            $existing->update(['quantity' => $targetQty]);

            return $existing->fresh(['variant.product.category', 'variant.attributeValues']);
        }

        /** @var CartItem $item */
        $item = $cart->items()->create([
            'product_variant_id' => $variantId,
            'quantity' => min($quantity, $maxAllowed),
        ]);

        return $item->load(['variant.product.category', 'variant.attributeValues']);
    }

    /**
     * Update cart item quantity.
     */
    public function updateQuantity(CartItem $item, int $quantity): ?CartItem
    {
        if ($quantity <= 0) {
            $item->delete();

            return null;
        }

        $variant = $item->variant;
        if ($variant) {
            $maxAllowed = min($variant->stock, 10);
            $quantity = min($quantity, $maxAllowed);
        }

        $item->update(['quantity' => $quantity]);

        return $item->fresh(['variant.product.category', 'variant.attributeValues']);
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    /**
     * Clear all items and coupon from the cart.
     */
    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->update(['coupon_id' => null]);
    }

    /**
     * Apply a discount coupon to the cart.
     */
    public function applyCoupon(Cart $cart, string $code): Coupon
    {
        $cleanCode = trim(strtoupper($code));
        $coupon = Coupon::query()->where('code', $cleanCode)->active()->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'code' => ['کد تخفیف وارد شده معتبر نیست یا منقضی شده است.'],
            ]);
        }

        $itemsSubtotal = (int) $cart->items->sum(fn (CartItem $i) => $i->subtotal);

        if (! $coupon->isValidFor($itemsSubtotal, $cart->user)) {
            if ($coupon->min_order_amount && $itemsSubtotal < $coupon->min_order_amount) {
                $minToman = number_format((float) ($coupon->min_order_amount / 10));
                throw ValidationException::withMessages([
                    'code' => ["حداقل مبلغ سفارش برای استفاده از این کد {$minToman} تومان می‌باشد."],
                ]);
            }

            throw ValidationException::withMessages([
                'code' => ['شرایط استفاده از این کد تخفیف برای سفارش شما برقرار نیست.'],
            ]);
        }

        $cart->update(['coupon_id' => $coupon->id]);

        return $coupon;
    }

    /**
     * Remove the active coupon from the cart.
     */
    public function removeCoupon(Cart $cart): void
    {
        $cart->update(['coupon_id' => null]);
    }

    /**
     * Merge guest cart into user cart after OTP login.
     */
    public function syncGuestCart(User $user, string $sessionId): Cart
    {
        return DB::transaction(function () use ($user, $sessionId): Cart {
            $userCart = $this->resolveCart($user);
            $guestCart = Cart::query()->where('session_id', $sessionId)->whereNull('user_id')->first();

            if (! $guestCart) {
                return $userCart;
            }

            $guestItems = $guestCart->items;

            foreach ($guestItems as $guestItem) {
                $existingItem = $userCart->items()
                    ->where('product_variant_id', $guestItem->product_variant_id)
                    ->first();

                if ($existingItem) {
                    $variant = $existingItem->variant;
                    $max = $variant ? min($variant->stock, 10) : 10;
                    $newQty = min($existingItem->quantity + $guestItem->quantity, $max);
                    $existingItem->update(['quantity' => $newQty]);
                } else {
                    $userCart->items()->create([
                        'product_variant_id' => $guestItem->product_variant_id,
                        'quantity' => $guestItem->quantity,
                    ]);
                }
            }

            // Transfer coupon if user has none
            if (! $userCart->coupon_id && $guestCart->coupon_id) {
                $userCart->update(['coupon_id' => $guestCart->coupon_id]);
            }

            $guestCart->items()->delete();
            $guestCart->delete();

            return $userCart->fresh(['items.variant.product', 'coupon']);
        });
    }
}
