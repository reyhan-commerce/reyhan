<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Resources\V1\CityResource;
use Reyhan\Core\Http\Resources\V1\ProvinceResource;
use Reyhan\Core\Models\Province;
use Illuminate\Http\JsonResponse;

final class GeoController extends Controller
{
    public function provinces(): JsonResponse
    {
        $provinces = Province::query()->active()->orderBy('order')->get();

        return ProvinceResource::collection($provinces)
            ->additional(['success' => true])
            ->response();
    }

    public function cities(Province $province): JsonResponse
    {
        $cities = $province->cities()->active()->orderBy('order')->get();

        return CityResource::collection($cities)
            ->additional(['success' => true])
            ->response();
    }
}
