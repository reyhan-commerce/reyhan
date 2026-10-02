<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Actions\Payment\VerifyPaymentAction;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Services\Payment\PaymentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PaymentController extends Controller
{
    /**
     * List available active payment gateways for customer.
     */
    public function gateways(PaymentManager $paymentManager): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $paymentManager->getActiveGateways(),
        ]);
    }

    /**
     * Verify payment transaction callback from bank gateway.
     */
    public function verify(
        Request $request,
        VerifyPaymentAction $verifyPaymentAction
    ): JsonResponse {
        $authority = (string) ($request->input('Authority') ?? $request->input('authority') ?? '');

        if (empty($authority)) {
            return response()->json([
                'success' => false,
                'message' => __('Payment authority tracking ID was not found in the request.'),
            ], 422);
        }

        $result = $verifyPaymentAction->execute($authority, $request->all());

        return response()->json([
            'success' => $result->success,
            'message' => $result->message,
            'data' => $result->toArray(),
        ], $result->success ? 200 : 400);
    }
}
