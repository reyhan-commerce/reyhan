<?php

declare(strict_types=1);

namespace Reyhan\Core\Enums;

use Reyhan\Core\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderReturnStatus: string implements HasColor, HasLabel
{
    use HasEnumHelpers;

    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case ItemReceived = 'item_received';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return __('enums.order_return_status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'info',
            self::Rejected => 'danger',
            self::ItemReceived => 'primary',
            self::Refunded => 'success',
            self::Cancelled => 'gray',
        };
    }

    public function color(): string
    {
        return $this->getColor();
    }
}
