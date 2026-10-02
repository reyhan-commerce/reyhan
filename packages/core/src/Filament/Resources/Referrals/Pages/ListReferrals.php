<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Referrals\Pages;

use Reyhan\Core\Filament\Resources\Referrals\ReferralResource;
use Filament\Resources\Pages\ListRecords;

class ListReferrals extends ListRecords
{
    protected static string $resource = ReferralResource::class;
}
