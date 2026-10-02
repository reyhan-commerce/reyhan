<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Reviews\Pages;

use Reyhan\Core\Filament\Resources\Reviews\ReviewResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReview extends CreateRecord
{
    protected static string $resource = ReviewResource::class;
}
