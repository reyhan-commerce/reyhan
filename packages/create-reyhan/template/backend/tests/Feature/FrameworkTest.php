<?php

declare(strict_types=1);

test('reyhan core service provider is registered', function () {
    expect(app()->providerIsLoaded(\Reyhan\Core\ReyhanServiceProvider::class))->toBeTrue();
});

test('health endpoint responds ok', function () {
    $response = $this->get('/up');
    $response->assertStatus(200);
});

test('api v1 products endpoint responds', function () {
    $response = $this->getJson('/api/v1/products');
    $response->assertStatus(200);
});

test('filament admin login route is accessible', function () {
    $response = $this->get('/admin/login');
    $response->assertStatus(200);
});
