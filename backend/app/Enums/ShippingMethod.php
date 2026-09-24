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
            self::Express => __('Express Courier (Tehran & Suburbs)'),
            self::Pishtaz => __('Nationwide Pishtaz Post'),
        };
    }

    public function deliveryTime(): string
    {
        return match ($this) {
            self::Express => __('Delivery in 1 to 3 hours'),
            self::Pishtaz => __('2 to 4 business days'),
        };
    }

    public function label(): string
    {
        return $this->title();
    }
}
