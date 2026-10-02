<?php

declare(strict_types=1);

namespace Tests\Feature\Marketing;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Reyhan\Core\Enums\BannerPosition;
use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Enums\ReferralStatus;
use Reyhan\Core\Models\AbandonedCartLog;
use Reyhan\Core\Models\Banner;
use Reyhan\Core\Models\Brand;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Models\CartItem;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\OrderItem;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\Referral;
use Reyhan\Core\Models\User;
use Reyhan\Core\Notifications\Marketing\AbandonedCartReminderNotification;
use Reyhan\Core\Services\Marketing\ReferralService;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'mobile' => '09121113344',
        'first_name' => 'علی',
        'last_name' => 'علوی',
        'is_active' => true,
        'wallet_balance' => 0,
    ]);

    $this->friend = User::factory()->create([
        'mobile' => '09125556677',
        'first_name' => 'رضا',
        'last_name' => 'رضایی',
        'is_active' => true,
        'wallet_balance' => 0,
    ]);

    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    $this->product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'is_active' => true,
    ]);

    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'stock' => 10,
        'price' => 1000000,
        'is_active' => true,
    ]);
});

test('public banners api returns active banners and filters by position', function (): void {
    Banner::factory()->create([
        'title' => 'بنر اسلایدر صفحه اصلی',
        'image_url' => 'https://example.com/slider.jpg',
        'position' => BannerPosition::HomeSlider,
        'order' => 1,
        'is_active' => true,
    ]);

    Banner::factory()->create([
        'title' => 'بنر منقضی شده',
        'image_url' => 'https://example.com/expired.jpg',
        'position' => BannerPosition::HomeSlider,
        'order' => 2,
        'is_active' => true,
        'ends_at' => now()->subDay(),
    ]);

    Banner::factory()->create([
        'title' => 'بنر میانی',
        'image_url' => 'https://example.com/middle.jpg',
        'position' => BannerPosition::HomeMiddle,
        'order' => 1,
        'is_active' => true,
    ]);

    $responseAll = $this->getJson('/api/v1/banners');
    $responseAll->assertOk()
        ->assertJsonCount(2, 'data');

    $responseSlider = $this->getJson('/api/v1/banners?position=home_slider');
    $responseSlider->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'بنر اسلایدر صفحه اصلی');
});

test('user auto-generates referral code and can claim friend code', function (): void {
    expect($this->user->referral_code)->not->toBeEmpty();
    expect($this->friend->referral_code)->not->toBeEmpty();

    // Friend claims user's referral code
    $response = $this->actingAs($this->friend, 'sanctum')
        ->postJson('/api/v1/profile/referral/claim', [
            'code' => $this->user->referral_code,
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    expect($this->friend->fresh()->referred_by)->toBe($this->user->id);

    $referral = Referral::query()
        ->where('referrer_id', $this->user->id)
        ->where('referred_id', $this->friend->id)
        ->first();

    expect($referral)->not->toBeNull();
    expect($referral->status)->toBe(ReferralStatus::Pending);

    // Cannot claim code twice
    $responseSecond = $this->actingAs($this->friend, 'sanctum')
        ->postJson('/api/v1/profile/referral/claim', [
            'code' => $this->user->referral_code,
        ]);
    $responseSecond->assertStatus(422);

    // User dashboard shows stats
    $dashResponse = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/profile/referral');

    $dashResponse->assertOk()
        ->assertJsonPath('data.total_referrals_count', 1)
        ->assertJsonPath('data.completed_referrals_count', 0);
});

test('completing an order rewards the referrer with wallet cashback', function (): void {
    $referralService = app(ReferralService::class);
    $referralService->applyReferralCode($this->friend, $this->user->referral_code);

    $order = Order::factory()->create([
        'user_id' => $this->friend->id,
        'status' => OrderStatus::Processing,
        'final_payable' => 2000000,
        'paid_at' => now(),
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'product_name' => $this->product->name,
        'variant_title' => 'پیش‌فرض',
        'sku' => 'SKU-REF-1',
        'quantity' => 2,
        'unit_price' => 1000000,
        'total_price' => 2000000,
        'final_price' => 2000000,
    ]);

    // Trigger reward settlement
    $completedRef = $referralService->rewardReferralUponOrderCompletion($order);

    expect($completedRef)->not->toBeNull();
    expect($completedRef->status)->toBe(ReferralStatus::Completed);
    expect($completedRef->order_id)->toBe($order->id);

    // Referrer wallet balance is increased by reward amount
    expect($this->user->fresh()->wallet_balance)->toBe(ReferralService::DEFAULT_REWARD_AMOUNT);
});

test('recover abandoned carts command identifies carts and dispatches notifications', function (): void {
    Notification::fake();

    $abandonedCart = Cart::factory()->create([
        'user_id' => $this->user->id,
        'updated_at' => now()->subHours(4),
    ]);

    CartItem::factory()->create([
        'cart_id' => $abandonedCart->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 1,
    ]);

    $this->artisan('cart:recover-abandoned', ['--hours' => 2])
        ->assertSuccessful();

    Notification::assertSentTo(
        $this->user,
        AbandonedCartReminderNotification::class,
        function (AbandonedCartReminderNotification $notification) use ($abandonedCart) {
            return $notification->cart->id === $abandonedCart->id;
        }
    );

    $log = AbandonedCartLog::query()
        ->where('cart_id', $abandonedCart->id)
        ->where('user_id', $this->user->id)
        ->first();

    expect($log)->not->toBeNull();
    expect($log->status)->toBe('sent');
});
