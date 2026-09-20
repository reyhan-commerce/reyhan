<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RequestOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Services\Otp\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Request OTP verification code (Captcha validated via FormRequest Rule).
     */
    public function requestOtp(RequestOtpRequest $request): JsonResponse
    {
        $mobile = (string) $request->input('mobile');

        // Generate OTP, store hash in Redis, and dispatch notification
        // Will throw OtpThrottledException (which renders JSON 429) if throttled
        $result = $this->otpService->generateAndSend($mobile);

        return response()->json([
            'success' => true,
            'message' => __('Verification code sent successfully.'),
            'data' => [
                'expires_in' => $result['expires_in'],
            ],
        ]);
    }

    /**
     * Verify OTP and issue Sanctum personal access token (OTP verified via FormRequest Rule).
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $mobile = (string) $request->input('mobile');
        $deviceName = (string) ($request->input('device_name') ?? 'web-client');

        // Invalidate OTP in Redis
        $this->otpService->clear($mobile);

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
