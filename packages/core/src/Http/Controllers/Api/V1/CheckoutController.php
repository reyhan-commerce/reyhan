<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Checkout\CreateOrderAction;
use App\Data\Checkout\CreateOrderData;
use App\Exceptions\Cart\EmptyCartException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Checkout\CreateOrderRequest;
use App\Models\Address;
use App\Models\User;
use App\Services\Cart\CartService;
use App\Services\Pricing\PricingService;
use App\Services\Shipping\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CheckoutController extends Controller
{
    /**
     * Preview checkout summary (subtotal, shipping, discounts, payable).
     */
    public function preview(
        Request $request,
        CartService $cartService,
        PricingService $pricingService,
        ShippingService $shippingService
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
                ->with(['city', 'province'])
                ->whereKey($addressId)
                ->first();
        }

        if (! $address) {
            $address = $user->defaultAddress()->with(['city', 'province'])->first();
        }

        $shippingMethodInput = $request->query('shipping_method_id') ?? $request->query('shipping_method');
        $pricing = $pricingService->calculateCart($cart, $address?->city, $shippingMethodInput);

        $couponGrantsFree = $pricing->isFreeShipping && $pricing->shippingFee === 0;
        $availableMethods = $shippingService->getAvailableMethods(
            subtotalRial: $pricing->itemsSubtotal,
            totalWeightGrams: $pricing->totalWeightGrams,
            destinationCity: $address?->city,
            couponGrantsFreeShipping: $couponGrantsFree
        );

        return response()->json([
            'success' => true,
            'data' => [
                'pricing' => $pricing->toArray(),
                'final_payable' => $pricing->finalPayable,
                'selected_address_id' => $address?->id,
                'items_count' => $pricing->totalItemsCount,
                'shipping_methods' => $availableMethods,
            ],
        ]);
    }

    /**
     * Get available shipping methods and delivery time slots for current cart.
     */
    public function shippingMethods(
        Request $request,
        CartService $cartService,
        ShippingService $shippingService,
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
                ->with(['city', 'province'])
                ->whereKey($addressId)
                ->first();
        }

        if (! $address) {
            $address = $user->defaultAddress()->with(['city', 'province'])->first();
        }

        $pricing = $pricingService->calculateCart($cart, $address?->city);
        $couponGrantsFree = $pricing->isFreeShipping && $pricing->shippingFee === 0;

        $methods = $shippingService->getAvailableMethods(
            subtotalRial: $pricing->itemsSubtotal,
            totalWeightGrams: $pricing->totalWeightGrams,
            destinationCity: $address?->city,
            couponGrantsFreeShipping: $couponGrantsFree
        );

        return response()->json([
            'success' => true,
            'data' => [
                'methods' => $methods,
                'selected_address_id' => $address?->id,
                'subtotal' => $pricing->itemsSubtotal,
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
            data: CreateOrderData::from($request->validated()),
        );

        return response()->json([
            'success' => true,
            'message' => __('Order created successfully and awaiting payment.'),
            'data' => [
                'order_id' => $result->order->id,
                'order_number' => $result->order->order_number,
                'final_payable' => $result->order->final_payable,
                'authority' => $result->payment->authority,
                'redirect_url' => $result->redirectUrl,
            ],
        ], 201);
    }
}
