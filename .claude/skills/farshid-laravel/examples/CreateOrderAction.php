<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Data\Orders\CreateOrderData;
use App\Enums\OrderStatusEnum;
use App\Events\OrderCreatedEvent;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateOrderAction
{
    /**
     * Execute the order creation business operation.
     */
    public function execute(User $user, CreateOrderData $data): Order
    {
        // 1. Orchestration & Transaction boundary
        return DB::transaction(function () use ($user, $data) {
            $subtotal = $data->calculateSubtotal();

            // 2. Create Order Aggregate Root
            $order = Order::create([
                'user_id' => $user->id,
                'status' => OrderStatusEnum::PENDING,
                'total_amount' => $subtotal,
                'discount_amount' => 0,
                'payment_method' => $data->paymentMethod,
                'notes' => $data->notes,
                'metadata' => [
                    'coupon' => $data->couponCode,
                ],
            ]);

            // 3. Create Line Items
            foreach ($data->items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            // 4. Dispatch domain event for asynchronous side effects (emails, inventory, analytics)
            OrderCreatedEvent::dispatch($order);

            return $order;
        });
    }
}
