<?php

declare(strict_types=1);

namespace Reyhan\Core\Enums;

use Reyhan\Core\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentGateway: string implements HasColor, HasIcon, HasLabel
{
    use HasEnumHelpers;

    case Sandbox = 'sandbox';
    case Zarinpal = 'zarinpal';
    case Saman = 'saman';
    case Mellat = 'mellat';
    case SnappPay = 'snapp_pay';
    case CardToCard = 'card_to_card';
    case Wallet = 'wallet';

    public function getLabel(): string
    {
        return __('enums.payment_gateway.'.$this->value);
    }

    public function title(): string
    {
        return $this->getLabel();
    }

    public function getDescription(): string
    {
        return __('enums.payment_gateway_description.'.$this->value);
    }

    public function description(): string
    {
        return $this->getDescription();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Zarinpal => 'warning',
            self::Saman => 'info',
            self::Mellat => 'danger',
            self::Sandbox => 'gray',
            self::SnappPay => 'secondary',
            self::CardToCard => 'primary',
            self::Wallet => 'success',
        };
    }

    public function color(): string
    {
        return $this->getColor();
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Sandbox => 'heroicon-o-beaker',
            self::Zarinpal => 'heroicon-o-shield-check',
            self::Saman, self::Mellat => 'heroicon-o-credit-card',
            self::SnappPay => 'heroicon-o-calendar-days',
            self::CardToCard => 'heroicon-o-arrows-right-left',
            self::Wallet => 'heroicon-o-wallet',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Sandbox => 'i-lucide-flask-conical',
            self::Zarinpal => 'i-lucide-shield-check',
            self::Saman, self::Mellat => 'i-lucide-credit-card',
            self::SnappPay => 'i-lucide-calendar-days',
            self::CardToCard => 'i-lucide-arrow-left-right',
            self::Wallet => 'i-lucide-wallet',
        };
    }
}
