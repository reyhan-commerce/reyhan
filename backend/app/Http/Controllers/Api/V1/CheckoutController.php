<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Checkout\CreateOrderAction;
use App\Exceptions\Cart\EmptyCartException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Checkout\CreateOrderRequest;
use App\Models\Address;
use App\Models\User;
use App\Services\Cart\CartService;
use App\Services\Pricing\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Preview checkout summary (subtotal, shipping, discounts, payable).
     */
    public function preview(
        Request $request,
        CartService $cartService,
        PricingService $pricingService
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $cart = $cartService->resolveCart($user);
        $cart->load(['items.variant.product', 'coupon']);

        if ($cart->items->isEmpty()) {
            throw new EmptyCartException;
        }

        $addressId = $request->query('address_id');
        $address = null;

        if ($addressId) {
            $address = Address::where('user_id', $user->id)
                ->with('city')
                ->find($addressId);
        }

        if (! $address) {
            $address = $user->defaultAddress()->with('city')->first();
        }

        $pricing = $pricingService->calculateCart($cart, $address?->city);

        return response()->json([
            'success' => true,
            'data' => [
                'pricing' => $pricing,
                'final_payable' => $pricing['final_payable'],
                'selected_address_id' => $address?->id,
                'items_count' => $pricing['total_items_count'],
            ],
        ]);
    }

    /**
     * Finalize checkout, lock stock, and create order.
     */
    public function createOrder(
        CreateOrderRequest $request,
        CreateOrderAction $createOrderAction
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $result = $createOrderAction->execute(
            user: $user,
            addressId: (int) $request->validated('address_id'),
            shippingMethodValue: (string) $request->validated('shipping_method'),
            gatewayValue: (string) $request->validated('gateway'),
            callbackUrl: (string) $request->validated('callback_url'),
            notes: $request->validated('notes')
        );

        return response()->json([
            'success' => true,
            'message' => __('Order created successfully and awaiting payment.'),
            'data' => [
                'order_id' => $result['order']->id,
                'order_number' => $result['order']->order_number,
                'final_payable' => $result['order']->final_payable,
                'authority' => $result['payment']->authority,
                'redirect_url' => $result['redirect_url'],
            ],
        ], 201);
    }
}
