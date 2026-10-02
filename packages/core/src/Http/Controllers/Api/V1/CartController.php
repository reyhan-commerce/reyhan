<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Api\V1\Concerns\ResolvesCart;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CartController extends Controller
{
    use ResolvesCart;

    public function __construct(
        protected CartService $cartService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);

        return $this->cartResponse($cart);
    }

    public function destroy(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $this->cartService->clearCart($cart);

        return $this->cartResponse($cart->refresh());
    }
}
