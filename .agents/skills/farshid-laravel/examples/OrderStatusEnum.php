<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    /**
     * Determine if the order has reached a terminal state.
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::PAID,
            self::CANCELLED,
            self::REFUNDED => true,
            default => false,
        };
    }

    /**
     * Get a human-readable display label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Payment',
            self::PROCESSING => 'Processing Order',
            self::PAID => 'Paid & Confirmed',
            self::CANCELLED => 'Order Cancelled',
            self::REFUNDED => 'Payment Refunded',
        };
    }

    /**
     * Get the badge color identifier for frontend UI.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'amber',
            self::PROCESSING => 'blue',
            self::PAID => 'emerald',
            self::CANCELLED => 'rose',
            self::REFUNDED => 'slate',
        };
    }
}
