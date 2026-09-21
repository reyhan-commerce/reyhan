<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Payment\VerifyPaymentAction;
use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
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
                'message' => 'شناسه پیگیری پرداخت (Authority) در درخواست یافت نشد.',
            ], 422);
        }

        $result = $verifyPaymentAction->execute($authority, $request->all());

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result,
        ], $result['success'] ? 200 : 400);
    }
}
