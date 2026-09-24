<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Captcha\SolveCaptchaRequest;
use App\Services\Captcha\CaptchaService;
use Illuminate\Http\JsonResponse;
use JsonException;

final class CaptchaController extends Controller
{
    public function __construct(
        protected CaptchaService $captchaService
    ) {}

    /**
     * Generate "I am not a robot" challenge.
     *
     * @throws JsonException
     */
    public function generate(): JsonResponse
    {
        $captcha = $this->captchaService->generate();

        return response()->json([
            'success' => true,
            'data' => $captcha,
        ]);
    }

    /**
     * Solve the challenge via interactive click (Validation & PoW handled via SolveCaptchaRequest).
     */
    public function solve(SolveCaptchaRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('Security verification succeeded.'),
        ]);
    }
}
