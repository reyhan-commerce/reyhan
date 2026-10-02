<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Payments\Pages;

use Reyhan\Core\Filament\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
}
