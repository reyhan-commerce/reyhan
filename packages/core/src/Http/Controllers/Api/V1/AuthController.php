<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Exceptions\User\UserDeactivatedException;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Requests\Api\V1\Auth\RequestOtpRequest;
use Reyhan\Core\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use Reyhan\Core\Http\Resources\V1\UserResource;
use Reyhan\Core\Models\User;
use Reyhan\Core\Services\Otp\OtpService;
use Reyhan\Core\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
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
