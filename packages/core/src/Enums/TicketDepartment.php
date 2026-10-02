<?php

declare(strict_types=1);

namespace Reyhan\Core\Enums;

use Reyhan\Core\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TicketDepartment: string implements HasColor, HasLabel
{
    use HasEnumHelpers;

    case Support = 'support';
    case Finance = 'finance';
    case Sales = 'sales';
    case Shipping = 'shipping';
    case Complaints = 'complaints';

    public function getLabel(): string
    {
        return __('enums.ticket_department.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Support => 'info',
            self::Finance => 'success',
            self::Sales => 'primary',
            self::Shipping => 'warning',
            self::Complaints => 'danger',
        };
    }

    public function color(): string
    {
        return $this->getColor();
    }
}
