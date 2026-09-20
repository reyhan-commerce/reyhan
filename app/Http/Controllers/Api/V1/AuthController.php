<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\User\UserDeactivatedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RequestOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Services\Otp\OtpService;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected UserService $userService
    ) {}

    /**
     * Request OTP verification code (Captcha validated via FormRequest Rule).
     */
    public function requestOtp(RequestOtpRequest $request): JsonResponse
    {
        $mobile = (string) $request->input('mobile');
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
     * Verify OTP and authenticate customer (OTP validated via FormRequest Rule).
     *
     * @throws UserDeactivatedException
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $mobile = (string) $request->input('mobile');
        $deviceName = (string) ($request->input('device_name') ?? 'web-client');

        $auth = $this->userService->authenticateWithOtp($mobile, $deviceName);

        return response()->json([
            'success' => true,
            'message' => __('Successfully logged in.'),
            'data' => [
                'token' => $auth['token'],
                'user' => UserResource::make($auth['user']),
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
            'data' => UserResource::make($user),
        ]);
    }
}
