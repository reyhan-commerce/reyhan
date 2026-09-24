<?php

declare(strict_types=1);

namespace App\Services\Loyalty;

use App\Enums\CouponScope;
use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class LoyaltyService
{
    public function __construct(
        protected GeneralSettings $settings
    ) {}

    /**
     * Award welcome bonus points on user signup / first verification.
     */
    public function awardSignupBonus(User $user): void
    {
        $bonus = $this->settings->loyalty_signup_bonus;
        if ($bonus <= 0) {
            return;
        }

        // Ensure user hasn't received signup bonus yet
        $alreadyAwarded = $user->loyaltyTransactions()
            ->where('type', 'signup_bonus')
            ->exists();

        if (! $alreadyAwarded) {
            $user->awardLoyaltyPoints(
                points: $bonus,
                type: 'signup_bonus',
                description: 'هدیه خوش‌آمدگویی و عضویت در ایزیشاپ'
            );
        }
    }

    /**
     * Award points for paid/delivered order based on rate per amount.
     */
    public function awardOrderReward(Order $order): void
    {
        $rate = $this->settings->loyalty_rate_amount_per_point;
        if ($rate <= 0 || ! $order->user_id) {
            return;
        }

        $user = $order->user;
        if (! $user) {
            return;
        }

        // Avoid duplicate reward for same order
        $alreadyAwarded = $user->loyaltyTransactions()
            ->where('type', 'order_reward')
            ->where('reference_id', (string) $order->order_number)
            ->exists();

        if ($alreadyAwarded) {
            return;
        }

        $points = (int) floor($order->final_payable / $rate);
        if ($points > 0) {
            $user->awardLoyaltyPoints(
                points: $points,
                type: 'order_reward',
                description: "پاداش ثبت موفق سفارش شماره {$order->order_number}",
                referenceId: (string) $order->order_number
            );
        }
    }

    /**
     * Convert available points into an active personal discount coupon.
     */
    public function redeemPoints(User $user, int $points): Coupon
    {
        if ($points <= 0) {
            throw new InvalidArgumentException('تعداد امتیاز برای تبدیل باید بزرگتر از صفر باشد.');
        }

        $currentBalance = $user->loyalty_points_balance;
        if ($currentBalance < $points) {
            throw new InvalidArgumentException("امتیاز کافی نیست. موجودی شما {$currentBalance} امتیاز است.");
        }

        $rateValue = $this->settings->loyalty_point_redemption_value;
        if ($rateValue <= 0) {
            $rateValue = 500; // default 500 Tomans per point
        }

        $discountAmount = $points * $rateValue;

        return DB::transaction(function () use ($user, $points, $discountAmount) {
            $code = 'CLUB-'.strtoupper(Str::random(6));

            $coupon = Coupon::create([
                'code' => $code,
                'title' => "کوپن پاداش باشگاه مشتریان ({$points} امتیاز)",
                'type' => CouponType::Fixed,
                'value' => $discountAmount,
                'min_order_amount' => $discountAmount * 2, // e.g. min purchase 2x discount
                'scope' => CouponScope::All,
                'usage_limit' => 1,
                'usage_limit_per_user' => 1,
                'starts_at' => now(),
                'expires_at' => now()->addDays(30),
                'is_active' => true,
            ]);

            $user->awardLoyaltyPoints(
                points: -$points,
                type: 'coupon_redemption',
                description: "تبدیل {$points} امتیاز به کد تخفیف {$code}",
                referenceId: $code
            );

            return $coupon;
        });
    }

    public function getEarnedPoints(User $user): int
    {
        return (int) $user->loyaltyTransactions()->where('points', '>', 0)->sum('points');
    }

    public function getSpentPoints(User $user): int
    {
        return abs((int) $user->loyaltyTransactions()->where('points', '<', 0)->sum('points'));
    }

    /**
     * @return array{balance: int, tier: array{key: string, label: string, color: string, icon: string, min_points: int, next_points: ?int, discount_percent: int}, progress: int, total_earned: int, total_spent: int, point_value: int, monetary_worth: int}
     */
    public function getSummary(User $user): array
    {
        $balance = $user->loyalty_points_balance;
        $tier = $user->loyalty_tier;
        $totalEarned = $this->getEarnedPoints($user);
        $totalSpent = $this->getSpentPoints($user);

        $progress = 100;
        if ($tier['next_points']) {
            $range = $tier['next_points'] - $tier['min_points'];
            $currentInRange = $balance - $tier['min_points'];
            $progress = min(100, max(0, (int) round(($currentInRange / $range) * 100)));
        }

        return [
            'balance' => $balance,
            'tier' => $tier,
            'progress' => $progress,
            'total_earned' => $totalEarned,
            'total_spent' => $totalSpent,
            'point_value' => $this->settings->loyalty_point_redemption_value,
            'monetary_worth' => $balance * $this->settings->loyalty_point_redemption_value,
        ];
    }
}
