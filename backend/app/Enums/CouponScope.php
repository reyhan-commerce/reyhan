<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CouponScope: string implements HasColor, HasLabel
{
    case All = 'all';
    case Categories = 'categories';
    case Brands = 'brands';
    case Variants = 'variants';

    public function getLabel(): string
    {
        return match ($this) {
            self::All => __('Entire Store & Cart'),
            self::Categories => __('Specific Categories'),
            self::Brands => __('Specific Brands'),
            self::Variants => __('Selected Products'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::All => 'success',
            self::Categories => 'primary',
            self::Brands => 'info',
            self::Variants => 'warning',
        };
    }
}
