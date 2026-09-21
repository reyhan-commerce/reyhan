<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'در انتظار پرداخت',
            self::Processing => 'در حال پردازش',
            self::Shipped => 'تحویل به پست / پیک',
            self::Delivered => 'تحویل داده شده',
            self::Cancelled => 'لغو شده',
            self::Refunded => 'مرجوع شده',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingPayment => 'warning',
            self::Processing => 'info',
            self::Shipped => 'primary',
            self::Delivered => 'success',
            self::Cancelled => 'neutral',
            self::Refunded => 'error',
        };
    }
}
