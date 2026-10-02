<?php

declare(strict_types=1);

namespace Reyhan\Core\Enums;

use Reyhan\Core\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    use HasEnumHelpers;

    case Pending = 'pending';
    case Success = 'success';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return __('enums.payment_status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Success => 'success',
            self::Failed => 'danger',
        };
    }

    public function color(): string
    {
        return $this->getColor();
    }
}
