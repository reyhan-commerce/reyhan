<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Http\Resources\V1\CartResource;
use App\Models\Cart;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ResolvesCart
{
    protected function getCart(Request $request): Cart
    {
        /** @var CartService $cartService */
        $cartService = app(CartService::class);
        $user = $request->user('sanctum');
        $sessionId = $request->header('X-Cart-Session') ?: $request->cookie('cart_session');

        return $cartService->resolveCart($user, $sessionId ? (string) $sessionId : null);
    }

    protected function cartResponse(Cart $cart, int $status = 200): JsonResponse
    {
        $headers = [];
        if ($cart->session_id) {
            $headers['X-Cart-Session'] = $cart->session_id;
        }

        return (new CartResource($cart))
            ->additional(['success' => true])
            ->response()
            ->setStatusCode($status)
            ->withHeaders($headers);
    }
}
