<?php

declare(strict_types=1);

namespace App\Data\Checkout;

use App\Models\Order;
use App\Models\Payment;
use Spatie\LaravelData\Data;

final class CreateOrderResultData extends Data
{
    public function __construct(
        public Order $order,
        public Payment $payment,
        public string $redirectUrl,
    ) {}
}
