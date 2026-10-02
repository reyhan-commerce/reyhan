<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Coupons\Pages;

use Reyhan\Core\Filament\Resources\Coupons\CouponResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
