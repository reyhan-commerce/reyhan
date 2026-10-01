<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use App\Models\User;
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
