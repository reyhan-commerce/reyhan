<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Accounting;

use Reyhan\Core\Actions\Accounting\CreateLedgerJournalEntryAction;
use Reyhan\Core\Models\LedgerAccount;
use Reyhan\Core\Models\LedgerTransaction;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;

final class LedgerService
{
    public function __construct(
        private readonly CreateLedgerJournalEntryAction $createJournalEntryAction,
    ) {}

    /**
     * Record a balanced double-entry journal transaction for an order settlement.
     */
    public function recordOrderSettlement(Order $order, Payment $payment): LedgerTransaction
    {
        return $this->createJournalEntryAction->execute($order, $payment);
    }

    /**
     * Get the net balance for a specific ledger account code.
     */
    public function getAccountBalance(string $code): int
    {
        /** @var LedgerAccount|null $account */
        $account = LedgerAccount::where('code', $code)->first();

        if (! $account) {
            return 0;
        }

        $debits = (int) $account->entries()->sum('debit');
        $credits = (int) $account->entries()->sum('credit');

        // Asset (1xxxx) and Expense (5xxxx) accounts are debit-normal; others are credit-normal
        if (str_starts_with($code, '1') || str_starts_with($code, '5')) {
            return $debits - $credits;
        }

        return $credits - $debits;
    }
}
