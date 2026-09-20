<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Captcha\CaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaptchaController extends Controller
{
    public function __construct(
        protected CaptchaService $captchaService
    ) {}

    /**
     * Generate "I am not a robot" challenge.
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
     * Solve the challenge via interactive click.
     */
    public function solve(Request $request): JsonResponse
    {
        $request->validate([
            'key' => ['required', 'string'],
            'nonce' => ['required', 'string'],
            'elapsed_ms' => ['required', 'integer', 'min:0'],
        ]);

        $key = (string) $request->input('key');
        $nonce = (string) $request->input('nonce');
        $elapsedMs = (int) $request->input('elapsed_ms');

        $passed = $this->captchaService->solve($key, $nonce, $elapsedMs);

        if (! $passed) {
            return response()->json([
                'success' => false,
                'message' => __('Security verification failed. Please try again.'),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => __('Security verification succeeded.'),
        ]);
    }
}
