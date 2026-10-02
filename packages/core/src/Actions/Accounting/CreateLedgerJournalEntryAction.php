<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Accounting;

use Reyhan\Core\Models\LedgerAccount;
use Reyhan\Core\Models\LedgerEntry;
use Reyhan\Core\Models\LedgerTransaction;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CreateLedgerJournalEntryAction
{
    /**
     * Issue balanced double-entry accounting journal entries for an order settlement.
     */
    public function execute(Order $order, Payment $payment): LedgerTransaction
    {
        return $this->recordOrderSettlement($order, $payment);
    }

    /**
     * Issue balanced double-entry accounting journal entries for an order settlement.
     */
    public function recordOrderSettlement(Order $order, Payment $payment): LedgerTransaction
    {
        return DB::transaction(function () use ($order, $payment): LedgerTransaction {
            $order->loadMissing('items');

            $tx = LedgerTransaction::create([
                'transaction_number' => LedgerTransaction::generateTransactionNumber(),
                'order_id' => $order->id,
                'reference_type' => 'Order',
                'reference_id' => $order->id,
                'description' => "سند ثبت فروش سفارش شماره {$order->order_number} با درگاه {$payment->gateway->value}",
                'transacted_at' => $payment->paid_at ?? now(),
            ]);

            $bankAccount = LedgerAccount::where('code', $payment->gateway->value === 'card_to_card' ? '10102' : '10101')->firstOrFail();
            $salesRevenueAccount = LedgerAccount::where('code', '40101')->firstOrFail();
            $shippingRevenueAccount = LedgerAccount::where('code', '40201')->firstOrFail();
            $vatPayableAccount = LedgerAccount::where('code', '20301')->firstOrFail();
            $discountExpenseAccount = LedgerAccount::where('code', '50101')->firstOrFail();
            $walletLiabilityAccount = LedgerAccount::where('code', '20101')->firstOrFail();

            $totalDebit = 0;
            $totalCredit = 0;

            // 1. DEBIT: Bank settlement amount
            if ($payment->amount > 0) {
                LedgerEntry::create([
                    'ledger_transaction_id' => $tx->id,
                    'ledger_account_id' => $bankAccount->id,
                    'debit' => $payment->amount,
                    'credit' => 0,
                    'memo' => "واریز درگاه شاپرک/کارت شماره پیگیری {$payment->tracking_code}",
                ]);
                $totalDebit += $payment->amount;
            }

            // 2. DEBIT: Wallet deduction amount
            if ($order->wallet_paid_amount > 0) {
                LedgerEntry::create([
                    'ledger_transaction_id' => $tx->id,
                    'ledger_account_id' => $walletLiabilityAccount->id,
                    'debit' => $order->wallet_paid_amount,
                    'credit' => 0,
                    'memo' => "کسر از کیف پول بابت سفارش {$order->order_number}",
                ]);
                $totalDebit += $order->wallet_paid_amount;
            }

            // 3. DEBIT: Coupon discounts granted
            if ($order->coupon_discount > 0) {
                LedgerEntry::create([
                    'ledger_transaction_id' => $tx->id,
                    'ledger_account_id' => $discountExpenseAccount->id,
                    'debit' => $order->coupon_discount,
                    'credit' => 0,
                    'memo' => "تخفیف کوپن {$order->coupon_code}",
                ]);
                $totalDebit += $order->coupon_discount;
            }

            // 4. CREDIT: Gross merchandise sales revenue
            if ($order->items_subtotal > 0) {
                LedgerEntry::create([
                    'ledger_transaction_id' => $tx->id,
                    'ledger_account_id' => $salesRevenueAccount->id,
                    'debit' => 0,
                    'credit' => $order->items_subtotal,
                    'memo' => "فروش ناخالص اقلام سفارش {$order->order_number}",
                ]);
                $totalCredit += $order->items_subtotal;
            }

            // 5. CREDIT: Shipping revenue
            if ($order->shipping_fee > 0) {
                LedgerEntry::create([
                    'ledger_transaction_id' => $tx->id,
                    'ledger_account_id' => $shippingRevenueAccount->id,
                    'debit' => 0,
                    'credit' => $order->shipping_fee,
                    'memo' => "درآمد حمل و ارسال سفارش {$order->order_number}",
                ]);
                $totalCredit += $order->shipping_fee;
            }

            // 6. CREDIT: VAT collected
            if ($order->tax_amount > 0) {
                LedgerEntry::create([
                    'ledger_transaction_id' => $tx->id,
                    'ledger_account_id' => $vatPayableAccount->id,
                    'debit' => 0,
                    'credit' => $order->tax_amount,
                    'memo' => "مالیات بر ارزش افزوده اخذ شده سفارش {$order->order_number}",
                ]);
                $totalCredit += $order->tax_amount;
            }

            // Verify double-entry balance: Debit must strictly equal Credit
            if ($totalDebit !== $totalCredit) {
                throw new InvalidArgumentException("سند حسابداری نامتوازن است: جمع بدهکار ({$totalDebit}) با جمع بستانکار ({$totalCredit}) برابر نیست.");
            }

            return $tx;
        });
    }
}
