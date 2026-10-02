<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\ContactMessages\Pages;

use Reyhan\Core\Filament\Resources\ContactMessages\ContactMessageResource;
use Filament\Resources\Pages\ListRecords;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;
}
