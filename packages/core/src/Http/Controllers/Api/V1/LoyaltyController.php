<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Exceptions\Feature\FeatureDisabledException;
use Reyhan\Core\Features\ShopFeature;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Requests\Api\V1\Loyalty\RedeemLoyaltyPointsRequest;
use Reyhan\Core\Models\User;
use Reyhan\Core\Services\Loyalty\LoyaltyService;
use Reyhan\Core\Settings\GeneralSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Laravel\Pennant\Feature;

final class LoyaltyController extends Controller
{
    public function __construct(
        protected LoyaltyService $loyaltyService,
        protected GeneralSettings $settings
    ) {}

    protected function ensureFeatureActive(): void
    {
        if (! Feature::active(ShopFeature::LOYALTY)) {
            throw new FeatureDisabledException;
        }
    }

    /**
     * Get user loyalty club summary.
     */
    public function summary(Request $request): JsonResponse
    {
        $this->ensureFeatureActive();

        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => $this->loyaltyService->getSummary($user),
        ]);
    }

    /**
     * Get user loyalty transaction history.
     */
    public function transactions(Request $request): JsonResponse
    {
        $this->ensureFeatureActive();

        /** @var User $user */
        $user = $request->user();

        $transactions = $user->loyaltyTransactions()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Redeem points into a discount coupon.
     */
    public function redeem(RedeemLoyaltyPointsRequest $request): JsonResponse
    {
        $this->ensureFeatureActive();

        /** @var User $user */
        $user = $request->user();

        try {
            $coupon = $this->loyaltyService->redeemPoints($user, (int) $request->validated('points'));

            return response()->json([
                'success' => true,
                'message' => __('Discount coupon :code issued successfully!', ['code' => $coupon->code]),
                'data' => [
                    'code' => $coupon->code,
                    'discount_amount' => $coupon->value,
                    'min_order_amount' => $coupon->min_order_amount,
                    'expires_at' => $coupon->expires_at?->toIso8601String(),
                    'new_balance' => $user->refresh()->loyalty_points_balance,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get VIP Club tier benefits and rules.
     */
    public function tiers(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'key' => 'bronze',
                    'label' => __('Bronze Tier'),
                    'min_points' => 0,
                    'max_points' => 499,
                    'icon' => 'i-lucide-shield',
                    'perks' => [
                        __('Earn points with every purchase and review'),
                        __('Receive welcome gift'),
                    ],
                ],
                [
                    'key' => 'silver',
                    'label' => __('Silver Tier'),
                    'min_points' => 500,
                    'max_points' => 1499,
                    'icon' => 'i-lucide-award',
                    'perks' => [
                        __('5% permanent additional discount on orders'),
                        __('Priority packaging and shipping'),
                        __('Special birthday discount coupon'),
                    ],
                ],
                [
                    'key' => 'gold',
                    'label' => __('Gold Tier (VIP)'),
                    'min_points' => 1500,
                    'max_points' => null,
                    'icon' => 'i-lucide-crown',
                    'perks' => [
                        __('Unconditional free shipping on all orders'),
                        __('10% additional VIP discount'),
                        __('Early access to seasonal sales'),
                        __('Dedicated phone support and skin consultation'),
                    ],
                ],
            ],
        ]);
    }
}
