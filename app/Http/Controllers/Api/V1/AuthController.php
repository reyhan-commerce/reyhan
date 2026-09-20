<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RequestOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Notifications\Auth\SendOtpNotification;
use App\Services\Captcha\CaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;

class AuthController extends Controller
{
    public function __construct(
        protected CaptchaService $captchaService
    ) {}

    /**
     * Request OTP verification code after verifying captcha.
     */
    public function requestOtp(RequestOtpRequest $request): JsonResponse
    {
        $mobile = (string) $request->input('mobile');
        $captchaKey = (string) $request->input('captcha_key');
        $captchaCode = (string) $request->input('captcha_code');

        // 1. Verify Captcha
        if (! $this->captchaService->verify($captchaKey, $captchaCode)) {
            return response()->json([
                'success' => false,
                'message' => 'کد امنیتی وارد شده نادرست یا منقضی شده است.',
                'errors' => [
                    'captcha_code' => ['کد امنیتی وارد شده نادرست یا منقضی شده است.'],
                ],
            ], 422);
        }

        // 2. Throttle check (1 OTP per 120 seconds)
        $throttleKey = "otp:throttle:{$mobile}";
        $redis = Redis::connection('default');

        if ($redis->get($throttleKey)) {
            $ttl = (int) $redis->ttl($throttleKey);

            return response()->json([
                'success' => false,
                'message' => "لطفاً {$ttl} ثانیه تا درخواست مجدد کد صبر کنید.",
            ], 429);
        }

        // 3. Generate 5-digit cryptographically secure OTP
        $code = (string) random_int(10000, 99999);

        // Store hashed OTP and throttle in Redis DB 0 with 120-second TTL
        $hashedCode = Hash::make($code);
        $redis->setex("otp:code:{$mobile}", 120, $hashedCode);
        $redis->setex($throttleKey, 120, '1');

        // 4. Dispatch SMS Notification via SmsChannel
        Notification::route('sms', $mobile)
            ->notify(new SendOtpNotification($code));

        return response()->json([
            'success' => true,
            'message' => 'کد تایید با موفقیت ارسال گردید.',
            'data' => [
                'expires_in' => 120,
            ],
        ]);
    }

    /**
     * Verify OTP and issue Sanctum personal access token.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $mobile = (string) $request->input('mobile');
        $code = (string) $request->input('code');
        $deviceName = (string) ($request->input('device_name') ?? 'web-client');

        $redis = Redis::connection('default');
        $storedHashedCode = (string) $redis->get("otp:code:{$mobile}");

        if (empty($storedHashedCode) || ! Hash::check($code, $storedHashedCode)) {
            return response()->json([
                'success' => false,
                'message' => 'کد تایید وارد شده نامعتبر یا منقضی شده است.',
                'errors' => [
                    'code' => ['کد تایید وارد شده نامعتبر یا منقضی شده است.'],
                ],
            ], 422);
        }

        // Invalidate OTP in Redis
        $redis->del("otp:code:{$mobile}");
        $redis->del("otp:throttle:{$mobile}");

        // Find or create customer
        /** @var User $user */
        $user = User::firstOrCreate(
            ['mobile' => $mobile],
            ['is_active' => true]
        );

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'حساب کاربری شما مسدود شده است.',
            ], 403);
        }

        if (! $user->mobile_verified_at) {
            $user->forceFill(['mobile_verified_at' => now()])->save();
        }

        // Generate Sanctum access token
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'با موفقیت وارد شدید.',
            'data' => [
                'token' => $token,
                'user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * Logout and revoke current token.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @phpstan-ignore-next-line */
        $user->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'با موفقیت خارج شدید.',
        ]);
    }

    /**
     * Get currently authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }
}
