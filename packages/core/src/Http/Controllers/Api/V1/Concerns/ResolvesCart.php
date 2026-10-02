<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1\Concerns;

use Reyhan\Core\Http\Resources\V1\CartResource;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ResolvesCart
{
    protected function getCart(Request $request): Cart
    {
        /** @var CartService $cartService */
        $cartService = app(CartService::class);
        $user = $request->user('sanctum');
        $rawSessionId = $request->header('X-Cart-Session') ?: $request->cookie('cart_session');
        $sessionId = is_string($rawSessionId) ? $rawSessionId : null;

        return $cartService->resolveCart($user, $sessionId);
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
