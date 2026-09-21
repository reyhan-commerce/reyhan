<?php

declare(strict_types=1);

namespace App\Enums;

enum ShippingMethod: string
{
    case Express = 'express';
    case Pishtaz = 'pishtaz';

    public function title(): string
    {
        return match ($this) {
            self::Express => 'پیک موتوری اکسپرس (تهران و حومه)',
            self::Pishtaz => 'پست پیشتاز سراسری',
        };
    }

    public function deliveryTime(): string
    {
        return match ($this) {
            self::Express => 'تحویل ۱ تا ۳ ساعته',
            self::Pishtaz => '۲ تا ۴ روز کاری',
        };
    }
}
