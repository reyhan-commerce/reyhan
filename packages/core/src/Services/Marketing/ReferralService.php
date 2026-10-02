<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Marketing;

use Reyhan\Core\Enums\ReferralStatus;
use Reyhan\Core\Enums\WalletTransactionType;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Referral;
use Reyhan\Core\Models\User;
use Reyhan\Core\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReferralService
{
    public const int DEFAULT_REWARD_AMOUNT = 500_000; // 50,000 Tomans in Rials

    public function __construct(
        private readonly WalletService $walletService,
    ) {}

    /**
     * Link user to referrer using a valid referral code.
     */
    public function applyReferralCode(User $user, string $code): Referral
    {
        $code = strtoupper(trim($code));

        /** @var User|null $referrer */
        $referrer = User::query()->where('referral_code', $code)->first();

        if (! $referrer) {
            throw ValidationException::withMessages([
                'code' => [__('messages.referral.invalid_code')],
            ]);
        }

        if ($referrer->id === $user->id) {
            throw ValidationException::withMessages([
                'code' => [__('messages.referral.self_referral')],
            ]);
        }

        if ($user->referred_by !== null) {
            throw ValidationException::withMessages([
                'code' => [__('messages.referral.already_referred')],
            ]);
        }

        return DB::transaction(function () use ($user, $referrer): Referral {
            $user->update(['referred_by' => $referrer->id]);

            return Referral::firstOrCreate(
                [
                    'referrer_id' => $referrer->id,
                    'referred_id' => $user->id,
                ],
                [
                    'status' => ReferralStatus::Pending,
                    'reward_amount' => self::DEFAULT_REWARD_AMOUNT,
                    'reward_type' => 'wallet',
                ]
            );
        });
    }

    /**
     * Settle referral reward upon first successful order.
     */
    public function rewardReferralUponOrderCompletion(Order $order): ?Referral
    {
        if (! $order->user_id) {
            return null;
        }

        /** @var Referral|null $referral */
        $referral = Referral::query()
            ->where('referred_id', $order->user_id)
            ->where('status', ReferralStatus::Pending)
            ->first();

        if (! $referral) {
            return null;
        }

        return DB::transaction(function () use ($referral, $order): Referral {
            $referral->update([
                'status' => ReferralStatus::Completed,
                'order_id' => $order->id,
                'completed_at' => now(),
            ]);

            /** @var User $referrer */
            $referrer = $referral->referrer;

            $rewardAmount = $referral->reward_amount > 0 ? $referral->reward_amount : self::DEFAULT_REWARD_AMOUNT;

            $this->walletService->deposit(
                user: $referrer,
                amountRial: $rewardAmount,
                description: __('messages.referral.reward_description', [
                    'user' => $order->user?->full_name ?? '',
                    'order' => $order->order_number,
                ]),
                type: WalletTransactionType::Cashback,
                orderId: $order->id,
                meta: [
                    'referral_id' => $referral->id,
                    'referred_user_id' => $order->user_id,
                ]
            );

            return $referral;
        });
    }
}
