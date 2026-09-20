<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AppSettingController;
use App\Settings\GeneralSettings;
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
                'instagram_url',
                'telegram_url',
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
