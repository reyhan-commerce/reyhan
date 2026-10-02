<?php

declare(strict_types=1);

namespace Reyhan\Core\Enums;

use Reyhan\Core\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReferralStatus: string implements HasColor, HasLabel
{
    use HasEnumHelpers;

    case Pending = 'pending';
    case Completed = 'completed';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return __('enums.referral_status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Completed => 'success',
            self::Expired => 'gray',
        };
    }

    public function color(): string
    {
        return $this->getColor();
    }
}
