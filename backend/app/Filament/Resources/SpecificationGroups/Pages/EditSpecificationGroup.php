<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpecificationGroups\Pages;

use App\Filament\Resources\SpecificationGroups\SpecificationGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpecificationGroup extends EditRecord
{
    protected static string $resource = SpecificationGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
