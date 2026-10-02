<?php

declare(strict_types=1);

namespace Reyhan\Core\Facades;

use Reyhan\Core\Models\LedgerTransaction;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Services\Accounting\LedgerService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static LedgerTransaction recordOrderSettlement(Order $order, Payment $payment)
 * @method static int getAccountBalance(string $code)
 *
 * @see \Reyhan\Core\Services\Accounting\LedgerService
 */
final class Ledger extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LedgerService::class;
    }
}
