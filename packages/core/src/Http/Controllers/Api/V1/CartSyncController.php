<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Api\V1\Concerns\ResolvesCart;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Requests\Api\V1\Cart\SyncCartRequest;
use Reyhan\Core\Models\User;
use Reyhan\Core\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;

final class CartSyncController extends Controller
{
    use ResolvesCart;

    public function __construct(
        protected CartService $cartService,
    ) {}

    public function __invoke(SyncCartRequest $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $mergedCart = $this->cartService->syncGuestCart($user, (string) $request->validated('session_id'));

        return $this->cartResponse($mergedCart);
    }
}
