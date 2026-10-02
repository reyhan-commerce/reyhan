<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Admins\Pages;

use Reyhan\Core\Filament\Resources\Admins\AdminResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;
}
