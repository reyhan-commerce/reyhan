<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Faqs\Pages;

use Reyhan\Core\Filament\Resources\Faqs\FaqResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFaq extends CreateRecord
{
    protected static string $resource = FaqResource::class;
}
