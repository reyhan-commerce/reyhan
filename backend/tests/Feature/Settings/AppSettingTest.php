<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AppSettingController;
use Illuminate\Support\Facades\Cache;

test('public settings endpoint returns store configuration and caches in redis', function () {
    Cache::forget(AppSettingController::CACHE_KEY);

    $response = $this->getJson('/api/v1/app/settings');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'store_name',
                'store_slogan',
                'support_phone',
                'support_email',
                'address',
                'postal_code',
                'free_shipping_threshold',
                'is_store_open',
                'work_hours',
                'whatsapp_url',
                'instagram_url',
                'telegram_url',
                'announcement_enabled',
                'announcement_text',
                'hero_badge_text',
                'hero_primary_button_text',
                'hero_secondary_button_text',
                'trust_badges',
                'categories_title',
                'categories_button_text',
                'flash_deals_title',
                'flash_deals_subtitle',
                'featured_products_title',
                'featured_products_button_text',
                'blog_title',
                'blog_button_text',
                'brands_title',
                'footer_copyright_text',
                'footer_designer_credit',
            ],
        ]);

    // Verify secret settings are never exposed
    $json = $response->json('data');
    expect($json)->not->toHaveKey('kavenegar_api_key')
        ->and($json)->not->toHaveKey('farazsms_api_key')
        ->and($json)->not->toHaveKey('ghasedak_api_key');

    // Verify cached in Redis
    expect(Cache::has(AppSettingController::CACHE_KEY))->toBeTrue();
});
