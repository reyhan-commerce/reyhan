<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Settings\AppSettingService;
use Illuminate\Http\JsonResponse;

class AppSettingController extends Controller
{
    public const string CACHE_KEY = AppSettingService::CACHE_KEY;

    public const string THEME_CACHE_KEY = AppSettingService::THEME_CACHE_KEY;

    public function __construct(
        protected AppSettingService $appSettingService
    ) {}

    /**
     * Get public store settings.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->appSettingService->getPublicSettings(),
        ]);
    }

    /**
     * Get public theme styling tokens.
     */
    public function theme(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->appSettingService->getThemeTokens(),
        ]);
    }
}
