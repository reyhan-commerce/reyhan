<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    /**
     * List user's orders.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $orders = $user->orders()
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    /**
     * Show single order details by order_number.
     */
    public function show(Request $request, string $orderNumber): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $order = Order::where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'payments'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }
}
