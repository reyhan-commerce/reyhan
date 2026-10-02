<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Orders;

use Reyhan\Core\Actions\Accounting\CreateLedgerJournalEntryAction;
use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Enums\PaymentStatus;
use Reyhan\Core\Models\CardTransferReceipt;
use Reyhan\Core\Models\Coupon;
use Reyhan\Core\Models\CouponUsage;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Services\Inventory\StockReservationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                $payment = $order->payments()->create([
                    'user_id' => $order->user_id,
                    'amount' => $receipt->amount,
                    'gateway' => PaymentGateway::CardToCard,
                    'status' => PaymentStatus::Success,
                    'reference_id' => $receipt->tracking_number,
                    'paid_at' => now(),
                ]);
            }

            // Record balanced double-entry accounting journal transaction
            try {
                app(CreateLedgerJournalEntryAction::class)->recordOrderSettlement($order, $payment);
            } catch (\Throwable $e) {
                Log::error("Failed to record ledger settlement for card approval order {$order->order_number}: {$e->getMessage()}");
            }
        });
    }
}
