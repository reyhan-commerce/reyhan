<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Pages\Pages;

use Reyhan\Core\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;
}
