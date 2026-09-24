<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CouponType: string implements HasColor, HasIcon, HasLabel
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';
    case FreeShipping = 'free_shipping';

    public function getLabel(): string
    {
        return match ($this) {
            self::Percentage => __('Percentage (%)'),
            self::Fixed => __('Fixed Amount (Rials)'),
            self::FreeShipping => __('Free Shipping'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Percentage => 'info',
            self::Fixed => 'success',
            self::FreeShipping => 'warning',
        };
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
