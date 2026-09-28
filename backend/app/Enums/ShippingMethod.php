<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ShippingMethod: string implements HasColor, HasLabel
{
    use HasEnumHelpers;

    case Express = 'express';
    case Pishtaz = 'pishtaz';

    public function getLabel(): string
    {
        return match ($this) {
            self::Express => __('enums.shipping_method.express_courier'),
            self::Pishtaz => __('enums.shipping_method.post_pishtaz'),
        };
    }

    public function title(): string
    {
        return $this->getLabel();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Express => 'warning',
            self::Pishtaz => 'primary',
        };
    }

    public function color(): string
    {
        return $this->getColor();
    }

    public function deliveryTime(): string
    {
        return match ($this) {
            self::Express => 'تحویل ۱ تا ۳ ساعته',
            self::Pishtaz => '۲ تا ۴ روز کاری',
        };
    }
}
