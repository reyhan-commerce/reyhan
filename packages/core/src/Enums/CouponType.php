<?php

declare(strict_types=1);

namespace Reyhan\Core\Enums;

use Reyhan\Core\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CouponType: string implements HasColor, HasIcon, HasLabel
{
    use HasEnumHelpers;

    case Percentage = 'percentage';
    case Fixed = 'fixed';
    case FreeShipping = 'free_shipping';

    public function getLabel(): string
    {
        return __('enums.coupon_type.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Percentage => 'info',
            self::Fixed => 'success',
            self::FreeShipping => 'warning',
        };
    }

    public function color(): string
    {
        return $this->getColor();
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Percentage => 'heroicon-o-variable',
            self::Fixed => 'heroicon-o-banknotes',
            self::FreeShipping => 'heroicon-o-truck',
        };
    }
}
