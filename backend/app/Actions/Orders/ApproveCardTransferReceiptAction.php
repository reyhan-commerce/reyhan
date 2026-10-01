<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Models\CardTransferReceipt;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\Inventory\StockReservationService;
use Illuminate\Support\Facades\DB;

final class ApproveCardTransferReceiptAction
{
    public function __construct(
        protected StockReservationService $stockReservationService,
    ) {}

    /**
     * Approve offline card transfer receipt, decrement stock atomically, and update order status.
     */
    public function execute(Order $order, CardTransferReceipt $receipt, ?int $adminId = null, ?string $adminNotes = null): void
    {
        DB::transaction(function () use ($order, $receipt, $adminId, $adminNotes): void {
            $receipt->update([
                'status' => 'approved',
                'reviewed_by' => $adminId ?? auth()->id(),
                'reviewed_at' => now(),
                'admin_notes' => $adminNotes,
            ]);

            // Tier 2 Pessimistic Database Concurrency Locking for variant stock
            $reservationId = $order->reservation_id ?? "order_{$order->id}";
            $order->loadMissing('items');
            foreach ($order->items as $item) {
                /** @var ProductVariant|null $variant */
                $variant = ProductVariant::where('id', $item->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if ($variant) {
                    $variant->decrement('stock', $item->quantity);
                    $this->stockReservationService->commit($variant->id, $item->quantity, $reservationId);
                }
            }

            $order->update([
                'status' => OrderStatus::Processing,
                'paid_at' => now(),
            ]);

            // Settle Coupon Usage if applied
            if (! empty($order->coupon_code)) {
                /** @var Coupon|null $coupon */
                $coupon = Coupon::where('code', $order->coupon_code)->lockForUpdate()->first();
                if ($coupon) {
                    CouponUsage::firstOrCreate(
                        [
                            'coupon_id' => $coupon->id,
                            'order_id' => $order->id,
                        ],
                        [
                            'user_id' => $order->user_id,
                            'discount_amount' => (int) $order->coupon_discount,
                        ]
                    );
                    $coupon->increment('used_count');
                }
            }

            $payment = $order->payments()->latest()->first();
            if ($payment) {
                $payment->update([
                    'status' => PaymentStatus::Success,
                    'paid_at' => now(),
                    'reference_id' => $receipt->tracking_number,
                ]);
            } else {
                $order->payments()->create([
                    'user_id' => $order->user_id,
                    'amount' => $receipt->amount,
                    'gateway' => PaymentGateway::CardToCard,
                    'status' => PaymentStatus::Success,
                    'reference_id' => $receipt->tracking_number,
                    'paid_at' => now(),
                ]);
            }
        });
    }
}
