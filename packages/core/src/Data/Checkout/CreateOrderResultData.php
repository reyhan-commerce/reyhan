<?php

declare(strict_types=1);

namespace Reyhan\Core\Data\Checkout;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Spatie\LaravelData\Data;

final class CreateOrderResultData extends Data
{
    public function __construct(
        public Order $order,
        public Payment $payment,
        public string $redirectUrl,
    ) {}
}
