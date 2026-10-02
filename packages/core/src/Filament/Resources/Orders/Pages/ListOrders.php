<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Orders\Pages;

use Reyhan\Core\Filament\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Zvizvi\FilamentColumnFilters\Concerns\HasColumnFilters;

class ListOrders extends ListRecords
{
    use HasColumnFilters;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
