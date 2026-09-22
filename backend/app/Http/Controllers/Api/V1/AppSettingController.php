<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Settings\GeneralSettings;
use App\Settings\ThemeSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AppSettingController extends Controller
{
    /**
     * Cache key for public store settings in Redis.
     */
    public const string CACHE_KEY = 'app:settings:general';

    /**
     * Cache TTL in seconds (24 hours).
     */
    protected const int TTL_SECONDS = 86400;

    public const string THEME_CACHE_KEY = 'app:settings:theme';

    public function __construct(
        protected GeneralSettings $settings,
        protected ThemeSettings $themeSettings
    ) {}

    /**
     * Get public store settings (cached in Redis).
     */
    public function index(): JsonResponse
    {
        /** @var array<string, mixed> $publicSettings */
        $publicSettings = Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, function () {
            $themeData = [
                'primary_color' => $this->themeSettings->primary_color,
                'secondary_color' => $this->themeSettings->secondary_color,
                'border_radius' => $this->themeSettings->border_radius,
                'spacing_scale' => $this->themeSettings->spacing_scale,
                'shadow_scale' => $this->themeSettings->shadow_scale,
                'blur_scale' => $this->themeSettings->blur_scale,
                'font_family' => $this->themeSettings->font_family,
                'font_scale' => $this->themeSettings->font_scale,
                'logo_light' => $this->themeSettings->logo_light,
                'logo_dark' => $this->themeSettings->logo_dark,
                'favicon' => $this->themeSettings->favicon,
            ];

            return [
                'store_name' => $this->settings->store_name,
                'store_slogan' => $this->settings->store_slogan,
                'store_logo' => $this->themeSettings->logo_light ?: $this->settings->store_logo,
                'store_favicon' => $this->themeSettings->favicon ?: $this->settings->store_favicon,
                'support_phone' => $this->settings->support_phone,
                'support_email' => $this->settings->support_email,
                'address' => $this->settings->address,
                'postal_code' => $this->settings->postal_code,
                'work_hours' => $this->settings->work_hours,
                'free_shipping_threshold' => $this->settings->free_shipping_threshold,
                'is_store_open' => $this->settings->is_store_open,
                'maintenance_message' => $this->settings->maintenance_message,
                'instagram_url' => $this->settings->instagram_url,
                'telegram_url' => $this->settings->telegram_url,
                'whatsapp_url' => $this->settings->whatsapp_url,
                'enamad_code' => $this->settings->enamad_code,
                'theme' => $themeData,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $publicSettings,
        ]);
    }

    /**
     * Get public theme styling tokens (cached in Redis).
     */
    public function theme(): JsonResponse
    {
        /** @var array<string, mixed> $themeTokens */
        $themeTokens = Cache::remember(self::THEME_CACHE_KEY, self::TTL_SECONDS, function () {
            return [
                'primary_color' => $this->themeSettings->primary_color,
                'secondary_color' => $this->themeSettings->secondary_color,
                'border_radius' => $this->themeSettings->border_radius,
                'spacing_scale' => $this->themeSettings->spacing_scale,
                'shadow_scale' => $this->themeSettings->shadow_scale,
                'blur_scale' => $this->themeSettings->blur_scale,
                'font_family' => $this->themeSettings->font_family,
                'font_scale' => $this->themeSettings->font_scale,
                'logo_light' => $this->themeSettings->logo_light,
                'logo_dark' => $this->themeSettings->logo_dark,
                'favicon' => $this->themeSettings->favicon,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $themeTokens,
        ]);
    }
}
