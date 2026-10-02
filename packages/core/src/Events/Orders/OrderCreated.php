<?php

declare(strict_types=1);

namespace Reyhan\Core\Events\Orders;

use Reyhan\Core\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OrderCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
    ) {}
}
