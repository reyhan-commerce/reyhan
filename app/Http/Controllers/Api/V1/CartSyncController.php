<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ResolvesCart;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\SyncCartRequest;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;

class CartSyncController extends Controller
{
    use ResolvesCart;

    public function __construct(
        protected CartService $cartService,
    ) {}

    public function __invoke(SyncCartRequest $request): JsonResponse
    {
        $user = $request->user();
        $mergedCart = $this->cartService->syncGuestCart($user, (string) $request->validated('session_id'));

        return $this->cartResponse($mergedCart);
    }
}
