<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\SearchProductsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Pipelines\Normalizer\PersianNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SearchSuggestionController extends Controller
{
    public function __construct(
        protected SearchProductsAction $searchAction,
    ) {}

    /**
     * Provide rapid multi-dimensional suggestions (products, categories, brands).
     */
    public function index(Request $request): JsonResponse
    {
        $rawQuery = (string) $request->input('q', '');
        $clean = PersianNormalizer::normalizeSearchQuery($rawQuery);

        if (mb_strlen($clean) < 2) {
            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $rawQuery,
                    'products' => [],
                    'categories' => [],
                    'brands' => [],
                ],
            ]);
        }

        // 1. Search top 5 products matching query
        $productsQuery = Product::query()
            ->active()
            ->with([
                'category',
                'brand',
                'activeVariants',
                'media',
            ]);

        $products = $this->searchAction
            ->execute($productsQuery, $clean)
            ->limit(5)
            ->get();

        // 2. Search top 4 active categories matching query
        $categories = Category::query()
            ->where('is_active', true)
            ->where('name', 'ILIKE', '%'.$clean.'%')
            ->orderByRaw('similarity(name, ?) DESC', [$clean])
            ->limit(4)
            ->get(['id', 'name', 'slug']);

        // 3. Search top 4 brands matching name or name_en
        $brands = Brand::query()
            ->where(function ($q) use ($clean): void {
                $q->where('name', 'ILIKE', '%'.$clean.'%')
                    ->orWhere('name_en', 'ILIKE', '%'.$clean.'%')
                    ->orWhereRaw('name % ?', [$clean]);
            })
            ->orderByRaw('similarity(name, ?) DESC', [$clean])
            ->limit(4)
            ->get(['id', 'name', 'slug']);

        return response()->json([
            'success' => true,
            'data' => [
                'query' => $rawQuery,
                'products' => ProductResource::collection($products),
                'categories' => $categories,
                'brands' => $brands,
            ],
        ]);
    }
}
