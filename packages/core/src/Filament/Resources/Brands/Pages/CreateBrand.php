<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Brands\Pages;

use Reyhan\Core\Filament\Resources\Brands\BrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;
}
