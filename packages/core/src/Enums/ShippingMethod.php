<?php

declare(strict_types=1);

namespace Reyhan\Core\Enums;

use Reyhan\Core\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ShippingMethod: string implements HasColor, HasLabel
{
    use HasEnumHelpers;

    case Express = 'express';
    case ExpressCourier = 'express_courier';
    case Pishtaz = 'pishtaz';
    case Tipax = 'tipax';
    case Freight = 'freight';

    public function getLabel(): string
    {
        return match ($this) {
            self::Express, self::ExpressCourier => __('enums.shipping_method.express_courier'),
            self::Pishtaz => __('enums.shipping_method.post_pishtaz'),
            self::Tipax => __('enums.shipping_method.tipax'),
            self::Freight => __('enums.shipping_method.freight'),
        };
    }

    public function title(): string
    {
        return $this->getLabel();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Express, self::ExpressCourier => 'warning',
            self::Pishtaz => 'primary',
            self::Tipax => 'info',
            self::Freight => 'gray',
        };
    }

    public function color(): string
    {
        return $this->getColor();
    }

    public function deliveryTime(): string
    {
        return match ($this) {
            self::Express, self::ExpressCourier => 'تحویل ۱ تا ۳ ساعته',
            self::Pishtaz => '۲ تا ۴ روز کاری',
            self::Tipax => '۱ تا ۲ روز کاری',
            self::Freight => '۳ تا ۵ روز کاری',
        };
    }
}
