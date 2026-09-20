<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\Auth\SendOtpNotification;
use App\Services\Captcha\CaptchaService;
use App\Services\Integrations\Kavenegar\KavenegarClient;
use App\Services\Sms\Drivers\FarazSmsDriver;
use App\Services\Sms\Drivers\GhasedakDriver;
use App\Services\Sms\Drivers\KavenegarDriver;
use App\Services\Sms\Drivers\LogDriver;
use App\Services\Sms\SmsManager;
use App\Settings\SmsSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Redis::connection('default')->flushdb();
    Queue::fake();
});

test('captcha generate endpoint returns key and svg', function () {
    $response = $this->getJson('/api/v1/captcha/generate');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => ['key', 'svg'],
        ]);
});

test('otp request fails when captcha is invalid', function () {
    $response = $this->postJson('/api/v1/auth/otp/request', [
        'mobile' => '09123456789',
        'captcha_key' => 'invalid-uuid',
        'captcha_code' => '999',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['captcha_code']);
});

test('otp request succeeds with valid captcha and dispatches sms notification', function () {
    Notification::fake();

    $captchaService = app(CaptchaService::class);
    $captcha = $captchaService->generate();
    $answer = Redis::connection('default')->get("captcha:{$captcha['key']}");

    $response = $this->postJson('/api/v1/auth/otp/request', [
        'mobile' => '09123456789',
        'captcha_key' => $captcha['key'],
        'captcha_code' => (string) $answer,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    // Verify OTP exists as a secure Bcrypt hash in Redis
    $storedHash = (string) Redis::connection('default')->get('otp:code:09123456789');
    expect($storedHash)->not->toBeEmpty()
        ->and(str_starts_with($storedHash, '$2y$'))->toBeTrue();

    // Verify Notification was dispatched
    Notification::assertSentOnDemand(
        SendOtpNotification::class,
        function (SendOtpNotification $notification, array $channels, $notifiable) use ($storedHash) {
            return $notifiable->routes['sms'] === '09123456789'
                && Hash::check($notification->code, $storedHash);
        }
    );
});

test('otp request is throttled when called multiple times within 120s', function () {
    Notification::fake();

    $captchaService = app(CaptchaService::class);

    // 1st request
    $captcha1 = $captchaService->generate();
    $answer1 = Redis::connection('default')->get("captcha:{$captcha1['key']}");
    $this->postJson('/api/v1/auth/otp/request', [
        'mobile' => '09123456789',
        'captcha_key' => $captcha1['key'],
        'captcha_code' => (string) $answer1,
    ])->assertOk();

    // 2nd request within 120 seconds
    $captcha2 = $captchaService->generate();
    $answer2 = Redis::connection('default')->get("captcha:{$captcha2['key']}");
    $response = $this->postJson('/api/v1/auth/otp/request', [
        'mobile' => '09123456789',
        'captcha_key' => $captcha2['key'],
        'captcha_code' => (string) $answer2,
    ]);

    $response->assertStatus(429);
});

test('otp verify checks hash, creates user and issues sanctum token', function () {
    $code = '12345';
    Redis::connection('default')->setex('otp:code:09123456789', 120, Hash::make($code));

    $response = $this->postJson('/api/v1/auth/otp/verify', [
        'mobile' => '۰۹۱۲۳۴۵۶۷۸۹', // Test Persian digits input
        'code' => '۱۲۳۴۵',         // Test Persian digits input
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

test('authenticated user can view profile and logout', function () {
    $user = User::create([
        'mobile' => '09129876543',
        'is_active' => true,
        'mobile_verified_at' => now(),
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
