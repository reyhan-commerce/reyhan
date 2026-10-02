<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Resources\V1\OrderResource;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

final class OrderInvoiceController extends Controller
{
    /**
     * Return invoice data for authenticated customer.
     * Route: GET /api/v1/orders/{orderNumber}/invoice (middleware: auth:sanctum)
     */
    public function show(Request $request, string $orderNumber): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'user', 'payments'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Return invoice data via a valid signed URL.
     * Route: GET /api/v1/orders/{orderNumber}/invoice/signed (middleware: signed:relative)
     */
    public function showSigned(string $orderNumber): JsonResponse
    {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'user', 'payments'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Generate a 2-hour signed invoice URL for admin use in Filament.
     */
    public static function generateAdminInvoiceUrl(Order $order): string
    {
        $apiSignedUrl = URL::temporarySignedRoute(
            'orders.invoice.signed',
            now()->addHours(2),
            ['orderNumber' => $order->order_number],
            false
        );

        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/');
        $query = parse_url($apiSignedUrl, PHP_URL_QUERY);

        return $frontendUrl.'/invoice/'.$order->order_number.'?'.$query;
    }
}
