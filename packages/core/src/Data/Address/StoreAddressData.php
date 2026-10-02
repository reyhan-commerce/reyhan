<?php

declare(strict_types=1);

namespace Reyhan\Core\Data\Address;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

final class StoreAddressData extends Data
{
    public function __construct(
        #[MapInputName('province_id')]
        public int $provinceId,

        #[MapInputName('city_id')]
        public int $cityId,

        #[MapInputName('recipient_name')]
        public string $recipientName,

        #[MapInputName('recipient_mobile')]
        public string $recipientMobile,

        #[MapInputName('postal_code')]
        public string $postalCode,

        #[MapInputName('address_line')]
        public string $addressLine,

        #[MapInputName('building_number')]
        public ?string $buildingNumber = null,

        public ?string $unit = null,

        #[MapInputName('is_default')]
        public ?bool $isDefault = null,
    ) {}
}
