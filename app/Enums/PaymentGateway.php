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
            self::Sandbox => 'درگاه پرداخت تستی (سندباکس)',
            self::Zarinpal => 'درگاه پرداخت زرین‌پال',
            self::Saman => 'درگاه پرداخت اینترنتی بانک سامان (سپ)',
            self::Mellat => 'درگاه پرداخت اینترنتی به پرداخت ملت',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Sandbox => 'شبیه‌ساز پرداخت جهت تست بدون نیاز به کارت واقعی',
            self::Zarinpal => 'پرداخت امن با کلیه کارت‌های عضو شتاب',
            self::Saman => 'اتصال مستقیم به درگاه پرداخت سپ',
            self::Mellat => 'پرداخت اینترنتی سریع با کلیه کارت‌های شتاب',
        };
    }
}
