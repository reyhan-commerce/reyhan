<?php

declare(strict_types=1);

namespace Reyhan\Core\Data\Payment;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class VerifyPaymentResultData extends Data
{
    public function __construct(
        public bool $success,
        public string $message,
        #[MapName('order_number')]
        public ?string $orderNumber = null,
        #[MapName('tracking_code')]
        public ?string $trackingCode = null,
        #[MapName('reference_id')]
        public ?string $referenceId = null,
        public ?int $amount = null,
        #[MapName('paid_at')]
        public ?string $paidAt = null,
    ) {}
}
