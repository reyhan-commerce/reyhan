<?php

declare(strict_types=1);

use App\Features\ShopFeature;
use App\Models\User;
use Laravel\Pennant\Feature;

test('loyalty tiers public endpoint returns tier levels and perks', function () {
    $response = $this->getJson('/api/v1/loyalty/tiers');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['key', 'label', 'min_points', 'icon', 'perks'],
            ],
        ]);
});

test('authenticated user can view loyalty summary with balance and tier', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $user->awardLoyaltyPoints(650, 'signup_bonus', 'هدیه ثبت‌نام');

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/loyalty/summary');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.balance', 650)
        ->assertJsonPath('data.tier.key', 'silver')
        ->assertJsonPath('data.tier.label', 'نقره‌ای');
});

test('user can redeem available points into a single-use coupon', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $user->awardLoyaltyPoints(200, 'order_reward', 'پاداش خرید قبلی');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/loyalty/redeem', [
            'points' => 100,
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.new_balance', 100);

    $code = $response->json('data.code');
    expect($code)->toStartWith('CLUB-');

    $this->assertDatabaseHas('coupons', [
        'code' => $code,
        'is_active' => true,
    ]);

    expect($user->fresh()->loyalty_points_balance)->toBe(100);
});

test('redeeming more points than user balance fails with 422 validation', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $user->awardLoyaltyPoints(50, 'signup_bonus', 'هدیه ثبت‌نام');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/loyalty/redeem', [
            'points' => 100,
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('loyalty endpoints return 403 when loyalty feature flag is deactivated', function () {
    Feature::define(ShopFeature::LOYALTY, fn () => false);

    /** @var User $user */
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/loyalty/summary');

    $response->assertForbidden();

    // Re-activate for other tests
    Feature::define(ShopFeature::LOYALTY, fn () => true);
});
