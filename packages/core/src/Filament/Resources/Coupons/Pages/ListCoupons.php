<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Coupons\Pages;

use Reyhan\Core\Filament\Resources\Coupons\CouponResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
