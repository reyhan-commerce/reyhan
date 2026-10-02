<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Resources\V1\PageResource;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Page;
use Reyhan\Core\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

final class PageController extends Controller
{
    /**
     * Get an active CMS static page by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $page = Page::active()->where('slug', $slug)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new PageResource($page),
        ]);
    }

    /**
     * Get sitemap index URLs cached for fast consumption.
     */
    public function sitemap(): JsonResponse
    {
        $urls = Cache::remember('sitemap_urls', 3600, function () {
            $list = [];

            // Static pages
            $pages = Page::active()->get(['slug', 'updated_at']);
            foreach ($pages as $p) {
                $list[] = [
                    'url' => "/pages/{$p->slug}",
                    'lastmod' => $p->updated_at?->toIso8601String(),
                    'priority' => 0.6,
                ];
            }

            // Categories
            $categories = Category::get(['slug', 'updated_at']);
            foreach ($categories as $c) {
                $list[] = [
                    'url' => "/categories/{$c->slug}",
                    'lastmod' => $c->updated_at?->toIso8601String(),
                    'priority' => 0.8,
                ];
            }

            // Products
            $products = Product::active()->get(['slug', 'updated_at']);
            foreach ($products as $pr) {
                $list[] = [
                    'url' => "/products/{$pr->slug}",
                    'lastmod' => $pr->updated_at?->toIso8601String(),
                    'priority' => 0.9,
                ];
            }

            return $list;
        });

        return response()->json([
            'success' => true,
            'data' => $urls,
        ]);
    }
}
