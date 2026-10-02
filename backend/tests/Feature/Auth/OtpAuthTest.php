<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Reyhan\Core\Models\User;
use Reyhan\Core\Notifications\Auth\SendOtpNotification;
use Reyhan\Core\Services\Captcha\CaptchaService;
use Reyhan\Core\Services\Integrations\Kavenegar\KavenegarClient;
use Reyhan\Core\Services\Otp\OtpService;
use Reyhan\Core\Services\Sms\Drivers\FarazSmsDriver;
use Reyhan\Core\Services\Sms\Drivers\GhasedakDriver;
use Reyhan\Core\Services\Sms\Drivers\KavenegarDriver;
use Reyhan\Core\Services\Sms\Drivers\LogDriver;
use Reyhan\Core\Services\Sms\SmsManager;
use Reyhan\Core\Settings\SmsSettings;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Redis::connection('default')->flushdb();
    Queue::fake();
});

test('captcha generate endpoint returns key, salt, and difficulty', function () {
    $response = $this->getJson('/api/v1/captcha/generate');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => ['key', 'salt', 'difficulty'],
        ]);
});

test('captcha solve validates pow and activates challenge token', function () {
    $captchaService = app(CaptchaService::class);
    $challenge = $captchaService->generate();

    // Compute valid nonce
    $nonce = 0;
    while (! str_starts_with(hash('sha256', $challenge['salt'].(string) $nonce), '0000')) {
        $nonce++;
    }

    $response = $this->postJson('/api/v1/captcha/solve', [
        'key' => $challenge['key'],
        'nonce' => (string) $nonce,
        'elapsed_ms' => 300,
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);
});

test('otp request fails when captcha token is missing or unverified', function () {
    $response = $this->postJson('/api/v1/auth/otp/request', [
        'mobile' => '09123456789',
        'captcha_token' => 'unverified-uuid',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['captcha_token']);
});

test('otp request succeeds with verified captcha and dispatches sms notification', function () {
    Notification::fake();

    $captchaService = app(CaptchaService::class);
    $challenge = $captchaService->generate();

    // Solve PoW
    $nonce = 0;
    while (! str_starts_with(hash('sha256', $challenge['salt'].(string) $nonce), '0000')) {
        $nonce++;
    }
    $captchaService->solve($challenge['key'], (string) $nonce, 250);

    $response = $this->postJson('/api/v1/auth/otp/request', [
        'mobile' => '09123456789',
        'captcha_token' => $challenge['key'],
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    // Verify OTP exists in Redis
    $storedHash = (string) Redis::connection('default')->get('otp:code:09123456789');
    expect($storedHash)->not->toBeEmpty();

    // Verify Notification was dispatched
    Notification::assertSentOnDemand(
        SendOtpNotification::class,
        function (SendOtpNotification $notification, array $channels, $notifiable) {
            return $notifiable->routes['sms'] === '09123456789'
                && app(OtpService::class)->check('09123456789', $notification->code);
        }
    );
});

test('otp request is throttled when called multiple times within 120s', function () {
    Notification::fake();

    $captchaService = app(CaptchaService::class);

    // 1st request
    $challenge1 = $captchaService->generate();
    $nonce1 = 0;
    while (! str_starts_with(hash('sha256', $challenge1['salt'].(string) $nonce1), '0000')) {
        $nonce1++;
    }
    $captchaService->solve($challenge1['key'], (string) $nonce1, 250);

    $this->postJson('/api/v1/auth/otp/request', [
        'mobile' => '09123456789',
        'captcha_token' => $challenge1['key'],
    ])->assertOk();

    // 2nd request within 120 seconds
    $challenge2 = $captchaService->generate();
    $nonce2 = 0;
    while (! str_starts_with(hash('sha256', $challenge2['salt'].(string) $nonce2), '0000')) {
        $nonce2++;
    }
    $captchaService->solve($challenge2['key'], (string) $nonce2, 250);

    $response = $this->postJson('/api/v1/auth/otp/request', [
        'mobile' => '09123456789',
        'captcha_token' => $challenge2['key'],
    ]);

    $response->assertStatus(429);
});

test('otp verify checks hash, creates user and issues sanctum token', function () {
    $code = '123456';
    app(OtpService::class)->generateAndSend('09123456789');

    $response = $this->postJson('/api/v1/auth/otp/verify', [
        'mobile' => '۰۹۱۲۳۴۵۶۷۸۹', // Test Persian digits input
        'code' => '۱۲۳۴۵۶',         // Test Persian digits input
        'device_name' => 'test-device',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'token',
                'user' => ['id', 'mobile', 'full_name'],
            ],
        ]);

    // Verify user in database
    $user = User::where('mobile', '09123456789')->first();
    expect($user)->not->toBeNull()
        ->and($user->mobile_verified_at)->not->toBeNull();

    // Verify OTP code was deleted from Redis
    expect(Redis::connection('default')->get('otp:code:09123456789'))->toBeNull();
});

test('otp verify fails when code is incorrect or expired', function () {
    app(OtpService::class)->generateAndSend('09123456789');

    $response = $this->postJson('/api/v1/auth/otp/verify', [
        'mobile' => '09123456789',
        'code' => '999999',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('otp verify fails with 403 when user is deactivated', function () {
    User::factory()->inactive()->create([
        'mobile' => '09121112233',
    ]);

    $otpData = app(OtpService::class)->generateAndSend('09121112233');

    $response = $this->postJson('/api/v1/auth/otp/verify', [
        'mobile' => '09121112233',
        'code' => $otpData['code'],
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'حساب کاربری شما مسدود شده است.',
        ]);
});

test('authenticated user can view profile and logout', function () {
    $user = User::factory()->create([
        'mobile' => '09129876543',
    ]);

    $token = $user->createToken('test')->plainTextToken;

    // Test /api/v1/auth/me
    $meResponse = $this->withToken($token)->getJson('/api/v1/auth/me');
    $meResponse->assertOk()
        ->assertJsonPath('data.mobile', '09129876543');

    // Test /api/v1/auth/logout
    $logoutResponse = $this->withToken($token)->postJson('/api/v1/auth/logout');
    $logoutResponse->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'با موفقیت خارج شدید.',
        ]);

    // Verify token was revoked from database
    expect($user->fresh()->tokens)->toHaveCount(0);
});

test('sms manager resolves drivers and clients via ioc container without manual new', function () {
    $smsManager = app(SmsManager::class);

    expect($smsManager->driver('log'))->toBeInstanceOf(LogDriver::class)
        ->and($smsManager->driver('kavenegar'))->toBeInstanceOf(KavenegarDriver::class)
        ->and($smsManager->driver('farazsms'))->toBeInstanceOf(FarazSmsDriver::class)
        ->and($smsManager->driver('ghasedak'))->toBeInstanceOf(GhasedakDriver::class);
});

test('sms clients reflect runtime updates to SmsSettings dynamically', function () {
    /** @var SmsSettings $settings */
    $settings = app(SmsSettings::class);
    $settings->kavenegar_sender = 'initial_sender';
    $settings->save();

    /** @var KavenegarClient $client */
    $client = app(KavenegarClient::class);

    // Update settings in database / runtime
    $settings->kavenegar_sender = 'updated_sender_live';
    $settings->save();

    // Client directly reads updated sender without restarting container
    Http::fake([
        'api.kavenegar.com/*' => Http::response(['status' => 200]),
    ]);

    $client->send('09123456789', 'تست پیامک');

    Http::assertSent(function (Request $request) {
        return $request['sender'] === 'updated_sender_live';
    });
});

test('sms manager switches active driver dynamically when SmsSettings active_driver changes', function () {
    /** @var SmsSettings $settings */
    $settings = app(SmsSettings::class);

    // Initial driver is log
    $settings->active_driver = 'log';
    $settings->save();

    /** @var SmsManager $manager1 */
    $manager1 = app(SmsManager::class);
    expect($manager1->driver())->toBeInstanceOf(LogDriver::class);

    // Change driver in settings to kavenegar
    $settings->active_driver = 'kavenegar';
    $settings->save();

    /** @var SmsManager $manager2 */
    $manager2 = app(SmsManager::class);
    expect($manager2->driver())->toBeInstanceOf(KavenegarDriver::class);

    // Change driver in settings to farazsms
    $settings->active_driver = 'farazsms';
    $settings->save();

    /** @var SmsManager $manager3 */
    $manager3 = app(SmsManager::class);
    expect($manager3->driver())->toBeInstanceOf(FarazSmsDriver::class);
});
