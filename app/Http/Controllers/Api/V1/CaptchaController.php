<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Captcha\CaptchaService;
use Illuminate\Http\JsonResponse;

class CaptchaController extends Controller
{
    public function __construct(
        protected CaptchaService $captchaService
    ) {}

    /**
     * Generate visual SVG captcha.
     */
    public function generate(): JsonResponse
    {
        $captcha = $this->captchaService->generate();

        return response()->json([
            'success' => true,
            'data' => $captcha,
        ]);
    }
}
