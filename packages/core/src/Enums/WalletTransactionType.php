<?php

declare(strict_types=1);

namespace Reyhan\Core\Enums;

use Reyhan\Core\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum WalletTransactionType: string implements HasColor, HasLabel
{
    use HasEnumHelpers;

    case Deposit = 'deposit';
    case Withdraw = 'withdraw';
    case Refund = 'refund';
    case Cashback = 'cashback';
    case AdminAdjustment = 'admin_adjustment';

    public function getLabel(): string
    {
        return __('enums.wallet_transaction_type.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Deposit, self::Refund, self::Cashback => 'success',
            self::Withdraw => 'danger',
            self::AdminAdjustment => 'warning',
        };
    }

    public function color(): string
    {
        return $this->getColor();
    }

    public function isCredit(): bool
    {
        return in_array($this, [self::Deposit, self::Refund, self::Cashback], true);
    }
}
