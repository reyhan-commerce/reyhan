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
        $captchaToken = (string) $request->input('captcha_token');

        // 1. Verify "I am not a robot" captcha token
        if (! $this->captchaService->verify($captchaToken)) {
            $msg = __('Security challenge is invalid or expired. Please click the checkbox again.');

            return response()->json([
                'success' => false,
                'message' => $msg,
                'errors' => [
                    'captcha_token' => [$msg],
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
                'message' => __('Please wait :seconds seconds before requesting another code.', ['seconds' => $ttl]),
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
            'message' => __('Verification code sent successfully.'),
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
            $msg = __('Verification code is invalid or has expired.');

            return response()->json([
                'success' => false,
                'message' => $msg,
                'errors' => [
                    'code' => [$msg],
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
                'message' => __('Your account has been deactivated.'),
            ], 403);
        }

        if (! $user->mobile_verified_at) {
            $user->forceFill(['mobile_verified_at' => now()])->save();
        }

        // Generate Sanctum access token
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('Successfully logged in.'),
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
            'message' => __('Successfully logged out.'),
        ]);
    }

    /**
     * Get authenticated customer profile.
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
