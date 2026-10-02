<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Categories\Pages;

use Alareqi\FilamentTree\Concerns\InteractsWithTreeTable;
use Reyhan\Core\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    use InteractsWithTreeTable;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('افزودن دسته‌بندی جدید'),
        ];
    }
}
