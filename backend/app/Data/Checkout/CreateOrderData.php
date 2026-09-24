<?php

declare(strict_types=1);

namespace App\Data\Checkout;

use App\Enums\PaymentGateway;
use App\Enums\ShippingMethod;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class CreateOrderData extends Data
{
    public function __construct(
        #[Required]
        #[MapInputName('address_id')]
        public int $addressId,

        #[Required]
        #[MapInputName('shipping_method')]
        public ShippingMethod $shippingMethod,

        #[Required]
        public PaymentGateway $gateway,

        #[Required]
        #[MapInputName('callback_url')]
        public string $callbackUrl,

        #[Max(500)]
        public ?string $notes = null,
    ) {}
}
