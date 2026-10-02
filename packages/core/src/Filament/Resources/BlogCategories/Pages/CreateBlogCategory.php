<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\BlogCategories\Pages;

use Reyhan\Core\Filament\Resources\BlogCategories\BlogCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogCategory extends CreateRecord
{
    protected static string $resource = BlogCategoryResource::class;
}
