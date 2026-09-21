<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Settings\GeneralSettings;
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

    public function __construct(
        protected GeneralSettings $settings
    ) {}

    /**
     * Get public store settings (cached in Redis).
     */
    public function index(): JsonResponse
    {
        /** @var array<string, mixed> $publicSettings */
        $publicSettings = Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, function () {
            return [
                'store_name' => $this->settings->store_name,
                'store_slogan' => $this->settings->store_slogan,
                'store_logo' => $this->settings->store_logo,
                'store_favicon' => $this->settings->store_favicon,
                'support_phone' => $this->settings->support_phone,
                'support_email' => $this->settings->support_email,
                'address' => $this->settings->address,
                'postal_code' => $this->settings->postal_code,
                'free_shipping_threshold' => $this->settings->free_shipping_threshold,
                'is_store_open' => $this->settings->is_store_open,
                'maintenance_message' => $this->settings->maintenance_message,
                'instagram_url' => $this->settings->instagram_url,
                'telegram_url' => $this->settings->telegram_url,
                'enamad_code' => $this->settings->enamad_code,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $publicSettings,
        ]);
    }
}
