<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Banners\Pages;

use Reyhan\Core\Filament\Resources\Banners\BannerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBanner extends CreateRecord
{
    protected static string $resource = BannerResource::class;
}
