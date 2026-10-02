<?php

declare(strict_types=1);

use Reyhan\Core\Actions\System\PerformCoreUpdateAction;
use Reyhan\Core\Data\System\UpdateResultData;
use Reyhan\Core\Features\ShopFeature;
use Reyhan\Core\Http\Controllers\Api\V1\AppFeaturesController;
use Illuminate\Support\Facades\Cache;

test('shop features enum contains all modular modules', function () {
    $names = ShopFeature::names();

    expect($names)->toContain(
        ShopFeature::REVIEWS,
        ShopFeature::COUPONS,
        ShopFeature::WISHLIST,
        ShopFeature::BRANDS,
        ShopFeature::STOCK_ALERTS,
        ShopFeature::COMPARISON,
        ShopFeature::BLOG,
        ShopFeature::LOYALTY,
        ShopFeature::WALLET,
        ShopFeature::REFERRAL,
        ShopFeature::RETURNS,
        ShopFeature::FAQ,
        ShopFeature::TICKETS,
        ShopFeature::QUESTIONS
    );
});

test('public features endpoint returns feature flags correctly', function () {
    Cache::forget(AppFeaturesController::CACHE_KEY);

    $response = $this->getJson(route('app.features'));

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'reviews',
                'coupons',
                'wishlist',
                'blog',
                'wallet',
            ],
        ]);
});

test('perform core update action executes safely', function () {
    $action = app(PerformCoreUpdateAction::class);
    $result = $action->execute(skipBackup: true);

    expect($result)->toBeInstanceOf(UpdateResultData::class)
        ->and($result->success)->toBeTrue()
        ->and($result->logs)->not->toBeEmpty();
});
