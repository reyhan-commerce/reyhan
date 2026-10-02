<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Enums\ReferralStatus;
use Reyhan\Core\Models\Referral;
use Reyhan\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Referral>
 */
class ReferralFactory extends Factory
{
    protected $model = Referral::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referrer_id' => User::factory(),
            'referred_id' => User::factory(),
            'order_id' => null,
            'status' => ReferralStatus::Pending,
            'reward_amount' => 500000,
            'reward_type' => 'wallet_credit',
            'completed_at' => null,
        ];
    }
}
