<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Enums\OrderReturnStatus;
use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\OrderItem;
use Reyhan\Core\Models\OrderReturn;
use Reyhan\Core\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\Jalalian;

final class OrderReturnController extends Controller
{
    /**
     * List user's return requests.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $returns = $user->orderReturns()
            ->with(['order', 'items.orderItem.product'])
            ->paginate((int) $request->query('per_page', 10));

        $data = $returns->through(function (OrderReturn $r): array {
            return [
                'id' => $r->id,
                'return_number' => $r->return_number,
                'order_number' => $r->order ? $r->order->order_number : '',
                'status' => $r->status->value,
                'status_label' => $r->status->label(),
                'status_color' => $r->status->color(),
                'reason' => $r->reason,
                'refund_amount' => $r->refund_amount,
                'refund_amount_toman' => (int) ($r->refund_amount / 10),
                'items_count' => $r->items->count(),
                'created_at' => $r->created_at?->toIso8601String() ?? '',
                'created_at_jalali' => $r->created_at ? Jalalian::fromCarbon($r->created_at)->format('Y/m/d H:i') : '',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get specific return request details.
     */
    public function show(Request $request, string $returnNumber): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $return = OrderReturn::query()
            ->where('user_id', $user->id)
            ->where('return_number', $returnNumber)
            ->with(['order', 'items.orderItem.product', 'items.orderItem.productVariant'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $return->id,
                'return_number' => $return->return_number,
                'order_number' => $return->order->order_number,
                'status' => $return->status->value,
                'status_label' => $return->status->label(),
                'status_color' => $return->status->color(),
                'reason' => $return->reason,
                'description' => $return->description,
                'photos' => $return->photos ?? [],
                'refund_method' => $return->refund_method,
                'refund_amount' => $return->refund_amount,
                'refund_amount_toman' => (int) ($return->refund_amount / 10),
                'admin_notes' => $return->admin_notes,
                'items' => $return->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->orderItem->product_name,
                    'variant_title' => $item->orderItem->variant_title,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'reason' => $item->reason,
                ]),
                'created_at' => $return->created_at->toIso8601String(),
                'created_at_jalali' => Jalalian::fromCarbon($return->created_at)->format('Y/m/d H:i'),
            ],
        ]);
    }

    /**
     * Submit a 7-day return request for an order.
     */
    public function store(Request $request, string $orderNumber): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->with('items')
            ->firstOrFail();

        // Check order delivery status
        if ($order->status !== OrderStatus::Delivered && $order->status !== OrderStatus::Shipped) {
            return response()->json([
                'success' => false,
                'message' => __('messages.returns.only_delivered_orders'),
            ], 422);
        }

        // Validate 7-day window based on delivered_at (ماده ۳۷ قانون تجارت الکترونیک)
        $deliveryDate = $order->delivered_at ?? $order->shipped_at;
        if ($deliveryDate && $deliveryDate->lt(now()->subDays(7))) {
            return response()->json([
                'success' => false,
                'message' => __('messages.returns.window_expired', ['default' => 'مهلت ۷ روزه قانونی حق انصراف و مرجوعی کالا به پایان رسیده است.']),
            ], 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:128'],
            'description' => ['nullable', 'string', 'max:1000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['string'],
            'refund_method' => ['nullable', 'string', 'in:wallet,bank_account'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.reason' => ['nullable', 'string', 'max:128'],
        ]);

        $orderReturn = DB::transaction(function () use ($user, $order, $validated): OrderReturn {
            $returnNumber = 'RMA-'.date('Ymd').'-'.strtoupper(Str::random(6));

            $totalRefund = 0;
            $itemsToCreate = [];

            foreach ($validated['items'] as $itemData) {
                /** @var OrderItem|null $orderItem */
                $orderItem = $order->items()->with('product')->find($itemData['order_item_id']);
                if (! $orderItem) {
                    continue;
                }

                // Check Article 38 non-returnable exceptions (مواد بهداشتی، فاسدشدنی، دیجیتال)
                if (! ($orderItem->product?->is_returnable ?? true)) {
                    $reasonText = $orderItem->product?->non_returnable_reason ?? 'کالاهای بهداشتی یا غیرقابل انصراف';
                    throw ValidationException::withMessages([
                        'items' => ["کالای '{$orderItem->product_name}' طبق ماده ۳۸ قانون تجارت الکترونیک ({$reasonText}) امکان مرجوعی ندارد."],
                    ]);
                }

                $qty = min((int) $itemData['quantity'], $orderItem->quantity);
                // Compute net line price after pro-rata coupon discount
                $netUnitPayable = (int) round((($orderItem->final_price * $orderItem->quantity) - ($orderItem->allocated_discount ?? 0)) / $orderItem->quantity);
                $linePrice = ($netUnitPayable + (int) round($orderItem->tax_amount / $orderItem->quantity)) * $qty;
                $totalRefund += $linePrice;

                $itemsToCreate[] = [
                    'order_item_id' => $orderItem->id,
                    'quantity' => $qty,
                    'price' => $linePrice,
                    'reason' => $itemData['reason'] ?? $validated['reason'],
                ];
            }

            /** @var OrderReturn $record */
            $record = OrderReturn::create([
                'return_number' => $returnNumber,
                'order_id' => $order->id,
                'user_id' => $user->id,
                'status' => OrderReturnStatus::Pending,
                'reason' => $validated['reason'],
                'description' => $validated['description'] ?? null,
                'photos' => $validated['photos'] ?? [],
                'refund_method' => $validated['refund_method'] ?? 'wallet',
                'refund_amount' => $totalRefund,
            ]);

            foreach ($itemsToCreate as $item) {
                $record->items()->create($item);
            }

            return $record;
        });

        return response()->json([
            'success' => true,
            'message' => __('messages.returns.submitted_success'),
            'data' => [
                'return_number' => $orderReturn->return_number,
                'status' => $orderReturn->status->value,
                'refund_amount' => $orderReturn->refund_amount,
            ],
        ], 201);
    }
}
