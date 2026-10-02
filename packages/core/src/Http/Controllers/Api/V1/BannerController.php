<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BannerController extends Controller
{
    /**
     * Get active banners, optionally filtered by position.
     * Route: GET /api/v1/banners
     */
    public function index(Request $request): JsonResponse
    {
        $query = Banner::query()->active()->with('media');

        if ($request->filled('position')) {
            $position = $request->query('position');
            $query->where('position', $position);
        }

        $banners = $query->get()->map(fn (Banner $banner) => [
            'id' => $banner->id,
            'title' => $banner->title,
            'subtitle' => $banner->subtitle,
            'image_url' => $banner->image_url,
            'mobile_image_url' => $banner->mobile_image_url ?: $banner->image_url,
            'link_url' => $banner->link_url,
            'position' => $banner->position->value,
            'order' => $banner->order,
        ]);

        return response()->json([
            'success' => true,
            'data' => $banners,
        ]);
    }
}
