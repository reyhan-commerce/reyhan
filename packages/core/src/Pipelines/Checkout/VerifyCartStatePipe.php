<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Checkout;

use Reyhan\Core\Models\Address;
use Reyhan\Core\Services\Cart\CartService;
use Closure;
use Illuminate\Validation\ValidationException;

final class VerifyCartStatePipe
{
    public function __construct(
        protected CartService $cartService,
    ) {}

    /**
     * @param  Closure(OrderCreationContext): mixed  $next
     */
    public function handle(OrderCreationContext $context, Closure $next): mixed
    {
        $context->address = Address::where('user_id', $context->user->id)
            ->with(['province', 'city'])
            ->findOrFail($context->data->addressId);

        $context->cart = $this->cartService->resolveCart($context->user);
        $context->cart->load(['items.productVariant.product', 'coupon']);

        if ($context->cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => [__('Your shopping cart is empty.')],
            ]);
        }

        foreach ($context->cart->items as $item) {
            $variant = $item->productVariant;

            if (! $variant || ! $variant->is_active) {
                throw ValidationException::withMessages([
                    'stock' => [__('Product \':product\' is currently not active.', ['product' => $item->productVariant?->product?->name])],
                ]);
            }
        }

        return $next($context);
    }
}
