<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Feature\FeatureDisabledException;
use App\Features\ShopFeature;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Loyalty\RedeemLoyaltyPointsRequest;
use App\Models\User;
use App\Services\Loyalty\LoyaltyService;
use App\Settings\GeneralSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Laravel\Pennant\Feature;

class LoyaltyController extends Controller
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
                    'label' => 'سطح برنزی',
                    'min_points' => 0,
                    'max_points' => 499,
                    'icon' => 'i-lucide-shield',
                    'perks' => [
                        'کسب امتیاز با هر خرید و نظر',
                        'دریافت هدیه خوش‌آمدگویی',
                    ],
                ],
                [
                    'key' => 'silver',
                    'label' => 'سطح نقره‌ای',
                    'min_points' => 500,
                    'max_points' => 1499,
                    'icon' => 'i-lucide-award',
                    'perks' => [
                        '۵٪ تخفیف مازاد دائمی روی سفارش‌ها',
                        'اولویت در ارسال و بسته‌بندی مرسولات',
                        'کد تخفیف ویژه سالروز تولد',
                    ],
                ],
                [
                    'key' => 'gold',
                    'label' => 'سطح طلایی (VIP)',
                    'min_points' => 1500,
                    'max_points' => null,
                    'icon' => 'i-lucide-crown',
                    'perks' => [
                        'ارسال رایگان برای تمامی سفارش‌ها بدون قید و شرط',
                        '۱۰٪ تخفیف مازاد VIP',
                        'دسترسی زودهنگام به حراجی‌های فصلی',
                        'پشتیبانی تلفنی و مشاور پوستی اختصاصی',
                    ],
                ],
            ],
        ]);
    }
}
