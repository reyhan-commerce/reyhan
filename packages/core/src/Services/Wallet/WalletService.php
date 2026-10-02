<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Wallet;

use Reyhan\Core\Enums\WalletTransactionType;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\User;
use Reyhan\Core\Models\WalletTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    /**
     * Credit the customer wallet (Deposit / Top-up / Refund / Cashback).
     *
     * @param  array<string, mixed>  $meta
     */
    public function deposit(
        User $user,
        int $amountRial,
        string $description,
        WalletTransactionType $type = WalletTransactionType::Deposit,
        ?int $orderId = null,
        array $meta = []
    ): WalletTransaction {
        if ($amountRial <= 0) {
            throw ValidationException::withMessages([
                'amount' => [__('Deposit amount must be greater than zero.')],
            ]);
        }

        return DB::transaction(function () use ($user, $amountRial, $description, $type, $orderId, $meta): WalletTransaction {
            // Lock user record for update to prevent concurrent race conditions
            /** @var User $lockedUser */
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            $newBalance = $lockedUser->wallet_balance + $amountRial;
            $lockedUser->update(['wallet_balance' => $newBalance]);
            $user->wallet_balance = $newBalance;

            return WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'order_id' => $orderId,
                'type' => $type,
                'amount' => $amountRial,
                'balance_after' => $newBalance,
                'description' => $description,
                'meta' => $meta,
            ]);
        });
    }

    /**
     * Debit the customer wallet (Order Payment).
     *
     * @param  array<string, mixed>  $meta
     */
    public function withdraw(
        User $user,
        int $amountRial,
        string $description,
        ?int $orderId = null,
        array $meta = []
    ): WalletTransaction {
        if ($amountRial <= 0) {
            throw ValidationException::withMessages([
                'amount' => [__('Withdrawal amount must be greater than zero.')],
            ]);
        }

        return DB::transaction(function () use ($user, $amountRial, $description, $orderId, $meta): WalletTransaction {
            /** @var User $lockedUser */
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->wallet_balance < $amountRial) {
                throw ValidationException::withMessages([
                    'wallet' => [__('Your wallet balance is insufficient for this transaction.')],
                ]);
            }

            $newBalance = $lockedUser->wallet_balance - $amountRial;
            $lockedUser->update(['wallet_balance' => $newBalance]);
            $user->wallet_balance = $newBalance;

            return WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'order_id' => $orderId,
                'type' => WalletTransactionType::Withdraw,
                'amount' => $amountRial,
                'balance_after' => $newBalance,
                'description' => $description,
                'meta' => $meta,
            ]);
        });
    }

    /**
     * Refund an order payment back to the customer's wallet.
     */
    public function refund(
        Order $order,
        ?int $amountRial = null,
        string $reason = 'استرداد وجه سفارش به کیف پول'
    ): WalletTransaction {
        /** @var User $user */
        $user = $order->user;

        $refundAmount = $amountRial ?? $order->final_payable;

        return $this->deposit(
            user: $user,
            amountRial: $refundAmount,
            description: "{$reason} (سفارش {$order->order_number})",
            type: WalletTransactionType::Refund,
            orderId: $order->id,
            meta: ['order_number' => $order->order_number]
        );
    }

    /**
     * Get real-time wallet balance for a user.
     */
    public function getBalance(User $user): int
    {
        return (int) User::query()->whereKey($user->id)->value('wallet_balance') ?? 0;
    }

    /**
     * Get paginated wallet transactions for a user.
     *
     * @return LengthAwarePaginator<WalletTransaction>
     */
    public function getTransactions(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return WalletTransaction::query()
            ->where('user_id', $user->id)
            ->with('order')
            ->latest('id')
            ->paginate($perPage);
    }
}
