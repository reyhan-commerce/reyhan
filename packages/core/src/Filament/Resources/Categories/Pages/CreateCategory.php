<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Categories\Pages;

use Reyhan\Core\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
