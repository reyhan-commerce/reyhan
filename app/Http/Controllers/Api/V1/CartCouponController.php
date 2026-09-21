<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ResolvesCart;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\ApplyCouponRequest;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartCouponController extends Controller
{
    use ResolvesCart;

    public function __construct(
        protected CartService $cartService,
    ) {}

    public function store(ApplyCouponRequest $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $this->cartService->applyCoupon($cart, (string) $request->validated('code'));

        return $this->cartResponse($cart->fresh());
    }

    public function destroy(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $this->cartService->removeCoupon($cart);

        return $this->cartResponse($cart->fresh());
    }
}
