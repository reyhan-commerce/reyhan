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

test('otp verify fails when code is incorrect or expired', function () {
    $code = '12345';
    Redis::connection('default')->setex('otp:code:09123456789', 120, Hash::make($code));

    $response = $this->postJson('/api/v1/auth/otp/verify', [
        'mobile' => '09123456789',
        'code' => '99999',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('otp verify fails with 403 when user is deactivated', function () {
    User::create([
        'mobile' => '09121112233',
        'is_active' => false,
    ]);

    $code = '12345';
    Redis::connection('default')->setex('otp:code:09121112233', 120, Hash::make($code));

    $response = $this->postJson('/api/v1/auth/otp/verify', [
        'mobile' => '09121112233',
        'code' => $code,
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'حساب کاربری شما مسدود شده است.',
        ]);
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
