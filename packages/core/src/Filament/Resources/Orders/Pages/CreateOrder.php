<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Orders\Pages;

use Reyhan\Core\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
