<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Enums\ReferralStatus;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Models\Referral;
use Reyhan\Core\Models\User;
use Reyhan\Core\Services\Marketing\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;

final class ReferralController extends Controller
{
    public function __construct(
        protected ReferralService $referralService,
    ) {}

    /**
     * Get customer's referral dashboard details.
     * Route: GET /api/v1/profile/referral
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Ensure user has a referral code
        if (empty($user->referral_code)) {
            $user->referral_code = strtoupper(Str::random(8));
            $user->save();
        }

        $referrals = Referral::query()
            ->where('referrer_id', $user->id)
            ->with(['referred', 'order'])
            ->latest()
            ->get();

        $completedReferrals = $referrals->where('status', ReferralStatus::Completed);
        $totalEarnedRial = (int) $completedReferrals->sum('reward_amount');

        $formattedReferrals = $referrals->map(function (Referral $ref) {
            $referredMobile = $ref->referred->mobile ?? '';
            // Mask mobile: 0912***3456
            $maskedMobile = strlen($referredMobile) >= 11
                ? substr($referredMobile, 0, 4).'***'.substr($referredMobile, -4)
                : $referredMobile;

            return [
                'id' => $ref->id,
                'referred_name' => $ref->referred?->full_name ?: 'کاربر دعوت‌شده',
                'referred_mobile' => $maskedMobile,
                'status' => $ref->status->value,
                'status_label' => $ref->status->label(),
                'status_color' => $ref->status->color(),
                'order_number' => $ref->order?->order_number,
                'reward_amount' => $ref->reward_amount,
                'reward_amount_toman' => (int) ($ref->reward_amount / 10),
                'completed_at' => $ref->completed_at?->toIso8601String(),
                'completed_at_jalali' => $ref->completed_at ? Jalalian::fromCarbon($ref->completed_at)->format('Y/m/d') : null,
                'created_at' => $ref->created_at->toIso8601String(),
                'created_at_jalali' => Jalalian::fromCarbon($ref->created_at)->format('Y/m/d'),
            ];
        });

        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $user->referral_code,
                'share_url' => "{$frontendUrl}/auth/login?ref={$user->referral_code}",
                'total_referrals_count' => $referrals->count(),
                'completed_referrals_count' => $completedReferrals->count(),
                'total_earned_rial' => $totalEarnedRial,
                'total_earned_toman' => (int) ($totalEarnedRial / 10),
                'reward_per_referral_rial' => ReferralService::DEFAULT_REWARD_AMOUNT,
                'reward_per_referral_toman' => (int) (ReferralService::DEFAULT_REWARD_AMOUNT / 10),
                'referred_by' => $user->referrer ? [
                    'name' => $user->referrer->full_name,
                    'code' => $user->referrer->referral_code,
                ] : null,
                'referrals' => $formattedReferrals,
            ],
        ]);
    }

    /**
     * Claim/Apply a referral code by the current user.
     * Route: POST /api/v1/profile/referral/claim
     */
    public function claim(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $referral = $this->referralService->applyReferralCode($user, $validated['code']);

        return response()->json([
            'success' => true,
            'message' => __('messages.referral.claimed_success'),
            'data' => [
                'referral_id' => $referral->id,
                'referrer_name' => $referral->referrer->full_name,
            ],
        ]);
    }
}
