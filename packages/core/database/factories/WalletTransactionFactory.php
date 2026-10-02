<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Enums\WalletTransactionType;
use Reyhan\Core\Models\User;
use Reyhan\Core\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_id' => null,
            'type' => WalletTransactionType::Deposit,
            'amount' => 1000000,
            'balance_after' => 1000000,
            'description' => 'شارژ حساب کیف پول',
            'meta' => null,
        ];
    }
}
