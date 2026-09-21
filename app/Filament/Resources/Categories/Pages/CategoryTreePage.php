<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use SolutionForest\FilamentTree\Resources\Pages\TreePage;

class CategoryTreePage extends TreePage
{
    protected static string $resource = CategoryResource::class;

    protected static ?string $title = 'نمایش درختی و ساختار دسته‌بندی‌ها';

    protected static int $maxDepth = 3;

    protected function getActions(): array
    {
        return [
            Action::make('list')
                ->label('بازگشت به نمایش جدولی')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(CategoryResource::getUrl('index')),
            $this->getCreateAction()
                ->label('افزودن دسته‌بندی جدید'),
        ];
    }

    protected function hasDeleteAction(): bool
    {
        return false;
    }

    protected function hasEditAction(): bool
    {
        return true;
    }
}
