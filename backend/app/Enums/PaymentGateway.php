<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentGateway: string
{
    case Sandbox = 'sandbox';
    case Zarinpal = 'zarinpal';
    case Saman = 'saman';
    case Mellat = 'mellat';

    public function title(): string
    {
        return match ($this) {
            self::Sandbox => __('Test Sandbox Gateway'),
            self::Zarinpal => __('Zarinpal Gateway'),
            self::Saman => __('Saman Bank (SEP) Online Gateway'),
            self::Mellat => __('Behpardakht Mellat Online Gateway'),
        };
    }

    public function label(): string
    {
        return $this->title();
    }

    public function description(): string
    {
        return match ($this) {
            self::Sandbox => __('Payment simulator for testing without real card'),
            self::Zarinpal => __('Secure payment with all Shetab cards'),
            self::Saman => __('Direct connection to SEP gateway'),
            self::Mellat => __('Fast online payment with all Shetab cards'),
        };
    }
}
