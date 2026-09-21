<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CityResource;
use App\Http\Resources\V1\ProvinceResource;
use App\Models\Province;
use Illuminate\Http\JsonResponse;

class GeoController extends Controller
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
