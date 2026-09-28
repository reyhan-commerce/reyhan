<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WalletTransactionType;
use App\Models\User;
use App\Models\WalletTransaction;
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
