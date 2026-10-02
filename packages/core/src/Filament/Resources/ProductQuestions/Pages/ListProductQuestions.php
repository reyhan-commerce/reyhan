<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\ProductQuestions\Pages;

use Reyhan\Core\Filament\Resources\ProductQuestions\ProductQuestionResource;
use Filament\Resources\Pages\ListRecords;

class ListProductQuestions extends ListRecords
{
    protected static string $resource = ProductQuestionResource::class;
}
