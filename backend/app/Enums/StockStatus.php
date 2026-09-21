<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StockStatus: string implements HasColor, HasIcon, HasLabel
{
    case InStock = 'in_stock';
    case LowStock = 'low_stock';
    case OutOfStock = 'out_of_stock';

    public function getLabel(): string
    {
        return match ($this) {
            self::InStock => 'موجود در انبار',
            self::LowStock => 'رو به اتمام',
            self::OutOfStock => 'ناموجود',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::InStock => 'success',
            self::LowStock => 'warning',
            self::OutOfStock => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::InStock => 'heroicon-o-check-circle',
            self::LowStock => 'heroicon-o-exclamation-triangle',
            self::OutOfStock => 'heroicon-o-x-circle',
        };
    }
}
