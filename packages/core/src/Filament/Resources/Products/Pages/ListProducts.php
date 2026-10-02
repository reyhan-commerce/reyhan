<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Products\Pages;

use Reyhan\Core\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Zvizvi\FilamentColumnFilters\Concerns\HasColumnFilters;

class ListProducts extends ListRecords
{
    use HasColumnFilters;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
