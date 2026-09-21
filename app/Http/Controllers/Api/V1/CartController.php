<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ResolvesCart;
use App\Http\Controllers\Controller;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
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

        return $this->cartResponse($cart->fresh());
    }
}
