<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\SpecificationGroups\Pages;

use Reyhan\Core\Filament\Resources\SpecificationGroups\SpecificationGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSpecificationGroup extends CreateRecord
{
    protected static string $resource = SpecificationGroupResource::class;
}
