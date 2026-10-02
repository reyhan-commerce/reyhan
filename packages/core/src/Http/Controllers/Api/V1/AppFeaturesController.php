<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Features\ShopFeature;
use Reyhan\Core\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Laravel\Pennant\Feature;

final class AppFeaturesController extends Controller
{
    public const string CACHE_KEY = 'app:features:active';

    protected const int TTL_SECONDS = 86400;

    /**
     * Get active platform feature flags.
     */
    public function index(): JsonResponse
    {
        /** @var array<string, bool> $features */
        $features = Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, function () {
            $result = [];
            foreach (ShopFeature::names() as $name) {
                $result[$name] = (bool) Feature::active($name);
            }

            return $result;
        });

        return response()->json([
            'success' => true,
            'data' => $features,
        ]);
    }
}
