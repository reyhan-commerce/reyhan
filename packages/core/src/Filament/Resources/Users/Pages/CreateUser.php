<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Users\Pages;

use Reyhan\Core\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
