<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Models\Brand;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class CatalogFiltersController extends Controller
{
    /**
     * Get dynamic filters metadata (brands, attributes, price range) for catalog view.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $categorySlug = $request->query('category');
        $cacheKey = 'catalog_filters_'.($categorySlug ?: 'all');

        $data = Cache::remember($cacheKey, 300, function () use ($categorySlug) {
            $productQuery = Product::query()->active();

            $category = null;
            if ($categorySlug) {
                $category = Category::query()->where('slug', $categorySlug)->first();
                if ($category) {
                    $categoryIds = $category->children()->pluck('id')->push($category->id)->all();
                    $productQuery->whereIn('category_id', $categoryIds);
                }
            }

            // Brands
            $brandIds = (clone $productQuery)->whereNotNull('brand_id')->distinct()->pluck('brand_id');
            $brands = Brand::query()
                ->whereIn('id', $brandIds)
                ->orderBy('name')
                ->get(['id', 'name', 'name_en', 'slug'])
                ->toArray();

            // Attributes
            $attributes = [];
            if ($category) {
                $categoryAttrs = $category->attributes()->with('values')->orderBy('order')->get();
                foreach ($categoryAttrs as $attr) {
                    $attributes[] = [
                        'id' => $attr->id,
                        'name' => $attr->name,
                        'slug' => $attr->slug,
                        'type' => $attr->type->value,
                        'values' => $attr->values->map(fn ($v) => [
                            'id' => $v->id,
                            'value' => $v->value,
                            'label' => $v->label,
                            'hex_code' => $v->hex_code,
                        ])->values()->all(),
                    ];
                }
            }

            // Price bounds
            $minPrice = (int) ProductVariant::query()->where('is_active', true)->min('price') ?: 0;
            $maxPrice = (int) ProductVariant::query()->where('is_active', true)->max('price') ?: 100000000;

            return [
                'brands' => $brands,
                'attributes' => $attributes,
                'price_bounds' => [
                    'min' => $minPrice,
                    'max' => $maxPrice,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
