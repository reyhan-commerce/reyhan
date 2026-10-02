<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\OrderReturns\Pages;

use Reyhan\Core\Filament\Resources\OrderReturns\OrderReturnResource;
use Filament\Resources\Pages\ViewRecord;

class ViewOrderReturn extends ViewRecord
{
    protected static string $resource = OrderReturnResource::class;
}
