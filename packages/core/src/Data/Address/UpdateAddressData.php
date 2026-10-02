<?php

declare(strict_types=1);

namespace Reyhan\Core\Data\Address;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

final class UpdateAddressData extends Data
{
    public function __construct(
        #[MapInputName('province_id')]
        public ?int $provinceId = null,

        #[MapInputName('city_id')]
        public ?int $cityId = null,

        #[MapInputName('recipient_name')]
        public ?string $recipientName = null,

        #[MapInputName('recipient_mobile')]
        public ?string $recipientMobile = null,

        #[MapInputName('postal_code')]
        public ?string $postalCode = null,

        #[MapInputName('address_line')]
        public ?string $addressLine = null,

        #[MapInputName('building_number')]
        public ?string $buildingNumber = null,

        public ?string $unit = null,

        #[MapInputName('is_default')]
        public ?bool $isDefault = null,
    ) {}
}
