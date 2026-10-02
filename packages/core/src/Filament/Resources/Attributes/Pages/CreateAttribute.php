<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Attributes\Pages;

use Reyhan\Core\Filament\Resources\Attributes\AttributeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttribute extends CreateRecord
{
    protected static string $resource = AttributeResource::class;
}
