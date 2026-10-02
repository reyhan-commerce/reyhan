<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Products\Pages;

use Reyhan\Core\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
