<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Services\Settings\AppSettingService;
use Illuminate\Http\JsonResponse;

final class AppSettingController extends Controller
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
