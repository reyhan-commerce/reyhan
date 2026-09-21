<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Checkout\CreateOrderAction;
use App\Enums\PaymentGateway;
use App\Enums\ShippingMethod;
use App\Http\Controllers\Controller;
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
            return response()->json([
                'success' => false,
                'message' => 'سبد خرید شما خالی است.',
            ], 422);
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
        Request $request,
        CreateOrderAction $createOrderAction
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'shipping_method' => ['required', 'string', 'in:'.implode(',', array_column(ShippingMethod::cases(), 'value'))],
            'gateway' => ['required', 'string', 'in:'.implode(',', array_column(PaymentGateway::cases(), 'value'))],
            'callback_url' => ['required', 'url'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'address_id.required' => 'انتخاب آدرس تحویل سفارش الزامی است.',
            'shipping_method.required' => 'انتخاب شیوه ارسال الزامی است.',
            'gateway.required' => 'انتخاب درگاه پرداخت الزامی است.',
            'callback_url.required' => 'آدرس بازگشت از درگاه پرداخت الزامی است.',
        ]);

        $result = $createOrderAction->execute(
            user: $user,
            addressId: (int) $validated['address_id'],
            shippingMethodValue: (string) $validated['shipping_method'],
            gatewayValue: (string) $validated['gateway'],
            callbackUrl: (string) $validated['callback_url'],
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'سفارش با موفقیت ثبت شد و در انتظار پرداخت است.',
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
