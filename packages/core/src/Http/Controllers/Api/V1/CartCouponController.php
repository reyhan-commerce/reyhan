<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Api\V1\Concerns\ResolvesCart;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Requests\Api\V1\Cart\ApplyCouponRequest;
use Reyhan\Core\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CartCouponController extends Controller
{
    use ResolvesCart;

    public function __construct(
        protected CartService $cartService,
    ) {}

    public function store(ApplyCouponRequest $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $this->cartService->applyCoupon($cart, (string) $request->validated('code'));

        return $this->cartResponse($cart->refresh());
    }

    public function destroy(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $this->cartService->removeCoupon($cart);

        return $this->cartResponse($cart->refresh());
    }
}
