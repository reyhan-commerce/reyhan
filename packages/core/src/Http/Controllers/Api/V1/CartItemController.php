<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ResolvesCart;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\AddCartItemRequest;
use App\Http\Requests\Api\V1\Cart\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CartItemController extends Controller
{
    use ResolvesCart;

    public function __construct(
        protected CartService $cartService,
    ) {}

    public function store(AddCartItemRequest $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $this->cartService->addItem(
            $cart,
            (int) $request->validated('variant_id'),
            (int) $request->input('quantity', 1)
        );

        return $this->cartResponse($cart->refresh(), 201);
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $cart = $this->getCart($request);
        abort_if($cartItem->cart_id !== $cart->id, 403);

        $this->cartService->updateQuantity($cartItem, (int) $request->validated('quantity'));

        return $this->cartResponse($cart->refresh());
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        $cart = $this->getCart($request);
        abort_if($cartItem->cart_id !== $cart->id, 403);

        $this->cartService->removeItem($cartItem);

        return $this->cartResponse($cart->refresh());
    }
}
