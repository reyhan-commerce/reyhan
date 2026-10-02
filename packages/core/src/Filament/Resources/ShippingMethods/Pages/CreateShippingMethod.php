<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\ShippingMethods\Pages;

use Reyhan\Core\Filament\Resources\ShippingMethods\ShippingMethodResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShippingMethod extends CreateRecord
{
    protected static string $resource = ShippingMethodResource::class;
}
