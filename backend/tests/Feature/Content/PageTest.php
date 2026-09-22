<?php

declare(strict_types=1);

use App\Models\Page;

test('page show endpoint returns metadata and content for active page', function () {
    Page::updateOrCreate(
        ['slug' => 'test-page'],
        [
            'title' => 'صفحه تست',
            'content' => 'محتوای تستی صفحه',
            'metadata' => ['key' => 'value', 'stats' => [['value' => '100', 'label' => 'آمار']]],
            'is_active' => true,
        ]
    );

    $response = $this->getJson('/api/v1/pages/test-page');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.title', 'صفحه تست')
        ->assertJsonPath('data.metadata.key', 'value')
        ->assertJsonPath('data.metadata.stats.0.value', '100');
});

test('inactive page returns 404', function () {
    Page::updateOrCreate(
        ['slug' => 'hidden-page'],
        [
            'title' => 'صفحه مخفی',
            'is_active' => false,
        ]
    );

    $response = $this->getJson('/api/v1/pages/hidden-page');
    $response->assertNotFound();
});
