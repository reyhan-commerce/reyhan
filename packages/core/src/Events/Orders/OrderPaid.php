<?php

declare(strict_types=1);

namespace Reyhan\Core\Events\Orders;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OrderPaid
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public Payment $payment,
    ) {}
}
