<?php

declare(strict_types=1);

it('defaults to Persian (fa) locale when no Accept-Language is provided', function (): void {
    $response = $this->postJson('/api/v1/payment/verify', []);

    $response->assertStatus(422)
        ->assertHeader('Content-Language', 'fa')
        ->assertJson([
            'success' => false,
            'message' => 'شناسه پیگیری پرداخت (Authority) در درخواست یافت نشد.',
        ]);
});

it('switches to English (en) when Accept-Language: en header is sent', function (): void {
    $response = $this->withHeader('Accept-Language', 'en')
        ->postJson('/api/v1/payment/verify', []);

    $response->assertStatus(422)
        ->assertHeader('Content-Language', 'en')
        ->assertJson([
            'success' => false,
            'message' => 'Payment authority tracking ID was not found in the request.',
        ]);
});

it('switches to English (en) when Accept-Language header contains en with q-factor', function (): void {
    $response = $this->withHeader('Accept-Language', 'en-US,en;q=0.9,fa;q=0.8')
        ->postJson('/api/v1/payment/verify', []);

    $response->assertStatus(422)
        ->assertHeader('Content-Language', 'en')
        ->assertJson([
            'success' => false,
            'message' => 'Payment authority tracking ID was not found in the request.',
        ]);
});

it('switches locale using query parameter ?lang=en', function (): void {
    $response = $this->postJson('/api/v1/payment/verify?lang=en', []);

    $response->assertStatus(422)
        ->assertHeader('Content-Language', 'en')
        ->assertJson([
            'success' => false,
            'message' => 'Payment authority tracking ID was not found in the request.',
        ]);
});

it('localizes loyalty tiers according to requested language', function (): void {
    // 1. Default Persian
    $faResponse = $this->getJson('/api/v1/loyalty/tiers');
    $faResponse->assertOk()
        ->assertHeader('Content-Language', 'fa');
    expect($faResponse->json('data.0.label'))->toBe('سطح برنزی')
        ->and($faResponse->json('data.2.label'))->toBe('سطح طلایی (VIP)');

    // 2. English requested
    $enResponse = $this->withHeader('Accept-Language', 'en')
        ->getJson('/api/v1/loyalty/tiers');
    $enResponse->assertOk()
        ->assertHeader('Content-Language', 'en');
    expect($enResponse->json('data.0.label'))->toBe('Bronze Tier')
        ->and($enResponse->json('data.2.label'))->toBe('Gold Tier (VIP)');
});
