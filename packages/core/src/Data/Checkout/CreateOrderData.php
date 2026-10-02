<?php

declare(strict_types=1);

namespace Reyhan\Core\Data\Checkout;

use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Enums\ShippingMethod;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class CreateOrderData extends Data
{
    /**
     * @param  array<string, mixed>|null  $corporateData
     */
    public function __construct(
        #[Required]
        #[MapInputName('address_id')]
        public int $addressId,

        #[Required]
        public PaymentGateway $gateway,

        #[Required]
        #[MapInputName('callback_url')]
        public string $callbackUrl,

        #[MapInputName('shipping_method_id')]
        public ?int $shippingMethodId = null,

        #[MapInputName('shipping_method')]
        public ShippingMethod|string|null $shippingMethod = null,

        #[MapInputName('delivery_date')]
        public ?string $deliveryDate = null,

        #[MapInputName('delivery_time_slot')]
        public ?string $deliveryTimeSlot = null,

        #[MapInputName('use_wallet')]
        public bool $useWallet = false,

        #[MapInputName('is_corporate_invoice')]
        public bool $isCorporateInvoice = false,

        #[MapInputName('corporate_data')]
        public ?array $corporateData = null,

        #[MapInputName('card_tracking_number')]
        public ?string $cardTrackingNumber = null,

        #[MapInputName('card_source_number')]
        public ?string $cardSourceNumber = null,

        #[Max(500)]
        public ?string $notes = null,
    ) {}
}
