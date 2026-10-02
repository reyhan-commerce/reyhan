<?php

declare(strict_types=1);

namespace Reyhan\Core\Console\Commands;

use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Services\Inventory\StockReservationService;
use Reyhan\Core\Services\Wallet\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class CancelExpiredPendingOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-expired {--minutes=30 : Age threshold in minutes for pending orders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancel pending orders older than the specified threshold, release stock reservations, and refund wallet deductions';

    /**
     * Execute the console command.
     */
    public function handle(
        StockReservationService $stockReservationService,
        WalletService $walletService
    ): int {
        $minutes = (int) $this->option('minutes');
        $threshold = now()->subMinutes($minutes);

        $expiredOrders = Order::query()
            ->where('status', OrderStatus::PendingPayment)
            ->where('created_at', '<=', $threshold)
            ->with(['items', 'user'])
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('No expired pending orders found.');

            return self::SUCCESS;
        }

        $this->info("Found {$expiredOrders->count()} expired pending orders. Processing cancellation...");

        $cancelledCount = 0;

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order, $stockReservationService, $walletService): void {
                /** @var Order $lockedOrder */
                $lockedOrder = Order::query()
                    ->lockForUpdate()
                    ->findOrFail($order->id);

                if ($lockedOrder->status !== OrderStatus::PendingPayment) {
                    return;
                }

                // 1. Release Redis stock reservations
                $reservationId = $lockedOrder->reservation_id ?? "order_{$lockedOrder->id}";
                foreach ($lockedOrder->items as $item) {
                    $stockReservationService->release(
                        variantId: $item->product_variant_id,
                        quantity: $item->quantity,
                        reservationId: $reservationId
                    );
                }

                // 2. Refund wallet deduction if partially deducted
                if ($lockedOrder->wallet_paid_amount > 0 && $lockedOrder->user) {
                    $walletService->deposit(
                        user: $lockedOrder->user,
                        amountRial: $lockedOrder->wallet_paid_amount,
                        description: "استرداد خودکار موجودی کیف پول بابت انقضای زمان پرداخت سفارش {$lockedOrder->order_number}",
                        orderId: $lockedOrder->id
                    );
                }

                // 3. Mark Order as Cancelled
                $lockedOrder->update([
                    'status' => OrderStatus::Cancelled,
                    'cancelled_at' => now(),
                ]);

                Log::info("Order [{$lockedOrder->order_number}] was automatically cancelled due to payment expiration.");
            });

            $cancelledCount++;
        }

        $this->info("Successfully cancelled {$cancelledCount} expired orders.");

        return self::SUCCESS;
    }
}
