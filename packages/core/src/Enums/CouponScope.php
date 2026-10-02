<?php

declare(strict_types=1);

namespace Reyhan\Core\Enums;

use Reyhan\Core\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CouponScope: string implements HasColor, HasLabel
{
    use HasEnumHelpers;

    case All = 'all';
    case Categories = 'categories';
    case Brands = 'brands';
    case Variants = 'variants';

    public function getLabel(): string
    {
        return __('enums.coupon_scope.'.$this->value);
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

    public function color(): string
    {
        return $this->getColor();
    }
}
