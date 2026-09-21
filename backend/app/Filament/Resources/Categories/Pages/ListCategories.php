<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('tree')
                ->label('نمایش ساختار درختی و جابجایی')
                ->icon('heroicon-o-bars-3-bottom-left')
                ->color('info')
                ->url(CategoryResource::getUrl('tree')),
            CreateAction::make()
                ->label('افزودن دسته‌بندی جدید'),
        ];
    }
}
