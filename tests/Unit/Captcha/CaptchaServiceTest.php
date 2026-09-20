<?php

declare(strict_types=1);

use App\Services\Captcha\CaptchaService;
use Illuminate\Support\Facades\Redis;

beforeEach(function () {
    // Clear Redis test keys
    Redis::connection('default')->flushdb();
});

test('generates valid svg captcha with uuid key', function () {
    $service = new CaptchaService;
    $result = $service->generate();

    expect($result)->toHaveKeys(['key', 'svg'])
        ->and($result['key'])->toBeString()->not->toBeEmpty()
        ->and($result['svg'])->toContain('<svg')->toContain('</svg>');
});

test('verifies correct captcha answer and deletes key', function () {
    $service = new CaptchaService;
    $result = $service->generate();
    $key = $result['key'];

    // Read stored answer directly from Redis
    $stored = Redis::connection('default')->get("captcha:{$key}");
    expect($stored)->not->toBeNull();

    // Verify with correct answer
    $isValid = $service->verify($key, (string) $stored);
    expect($isValid)->toBeTrue();

    // Second attempt must fail because key is one-time use
    expect($service->verify($key, (string) $stored))->toBeFalse();
});

test('accepts Persian numerals for captcha answer', function () {
    $service = new CaptchaService;
    $result = $service->generate();
    $key = $result['key'];

    Redis::connection('default')->setex("captcha:{$key}", 120, '14');

    // User submits Persian digits '۱۴'
    expect($service->verify($key, '۱۴'))->toBeTrue();
});
