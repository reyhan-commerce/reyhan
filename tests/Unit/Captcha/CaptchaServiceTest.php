<?php

declare(strict_types=1);

use App\Services\Captcha\CaptchaService;
use Illuminate\Support\Facades\Redis;

beforeEach(function () {
    Redis::connection('default')->flushdb();
});

test('generates valid PoW challenge for interactive robot check', function () {
    $service = new CaptchaService;
    $result = $service->generate();

    expect($result)->toHaveKeys(['key', 'salt', 'difficulty'])
        ->and($result['key'])->toBeString()->not->toBeEmpty()
        ->and($result['salt'])->toBeString()->not->toBeEmpty()
        ->and($result['difficulty'])->toBeInt()->toBe(4);
});

test('solves PoW challenge and verifies one-time token', function () {
    $service = new CaptchaService;
    $challenge = $service->generate();

    $salt = $challenge['salt'];
    $targetPrefix = '0000';

    // Solve PoW in test runner
    $nonce = 0;
    while (true) {
        $hash = hash('sha256', $salt.(string) $nonce);
        if (str_starts_with($hash, $targetPrefix)) {
            break;
        }
        $nonce++;
    }

    // Solve with human speed simulation (>= 100ms)
    $passed = $service->solve($challenge['key'], (string) $nonce, 250);
    expect($passed)->toBeTrue();

    // Verify token consumed
    expect($service->verify($challenge['key']))->toBeTrue();

    // Second verify attempt must fail (one-time use)
    expect($service->verify($challenge['key']))->toBeFalse();
});

test('rejects bot when elapsed time is unrealistically fast', function () {
    $service = new CaptchaService;
    $challenge = $service->generate();

    $passed = $service->solve($challenge['key'], '123', 50); // < 100ms
    expect($passed)->toBeFalse();
});
