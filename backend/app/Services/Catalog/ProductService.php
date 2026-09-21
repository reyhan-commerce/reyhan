<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Actions\SearchProductsAction;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    public function __construct(
        protected SearchProductsAction $searchAction,
    ) {}

    /**
     * List products with multi-dimensional filtering, search, and sorting.
     *
     * @param  array{
     *     category?: string|null,
     *     brand?: string|array<string>|null,
     *     min_price?: int|string|null,
     *     max_price?: int|string|null,
     *     in_stock?: bool|string|null,
     *     search?: string|null,
     *     sort?: string|null,
     * }  $filters
     * @return LengthAwarePaginator<Product>
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::query()
            ->active()
            ->with([
                'category',
                'brand',
                'activeVariants.attributeValues',
                'media',
            ]);

        // 1. Search term (pg_trgm fuzzy search)
        if (! empty($filters['search'])) {
            $query = $this->searchAction->execute($query, (string) $filters['search']);
        }

        // 2. Category filter (including child categories)
        if (! empty($filters['category'])) {
            $categorySlug = (string) $filters['category'];
            $category = Category::query()->where('slug', $categorySlug)->first();

            if ($category) {
                $categoryIds = $category->children()->pluck('id')->push($category->id)->all();
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // 3. Brand filter (single or array of slugs)
        if (! empty($filters['brand'])) {
            $brands = is_array($filters['brand']) ? $filters['brand'] : explode(',', (string) $filters['brand']);
            $query->whereHas('brand', function (Builder $b) use ($brands): void {
                $b->whereIn('slug', $brands);
            });
        }

        // 4. Price range filter (on variants)
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $minPrice = (int) $filters['min_price'];
            $query->whereHas('activeVariants', function (Builder $v) use ($minPrice): void {
                $v->where('price', '>=', $minPrice);
            });
        }

        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $maxPrice = (int) $filters['max_price'];
            $query->whereHas('activeVariants', function (Builder $v) use ($maxPrice): void {
                $v->where('price', '<=', $maxPrice);
            });
        }

        // 5. In-stock toggle
        if (! empty($filters['in_stock']) && filter_var($filters['in_stock'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas('activeVariants', function (Builder $v): void {
                $v->where('stock', '>', 0);
            });
        }

        // 6. Sorting (when not sorted by search similarity)
        if (empty($filters['search'])) {
            $sort = $filters['sort'] ?? 'latest';
            match ($sort) {
                'cheapest' => $query->orderBy(
                    ProductVariant::select('price')
                        ->whereColumn('product_variants.product_id', 'products.id')
                        ->where('is_active', true)
                        ->orderBy('price', 'asc')
                        ->limit(1),
                    'asc'
                ),
                'expensive' => $query->orderBy(
                    ProductVariant::select('price')
                        ->whereColumn('product_variants.product_id', 'products.id')
                        ->where('is_active', true)
                        ->orderBy('price', 'desc')
                        ->limit(1),
                    'desc'
                ),
                'featured' => $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc'),
                default => $query->orderBy('created_at', 'desc'),
            };
        }

        return $query->paginate($perPage);
    }

    /**
     * Find active product by slug with full variant matrix relations.
     */
    public function findBySlug(string $slug): Product
    {
        return Product::query()
            ->where('slug', $slug)
            ->active()
            ->with([
                'category.attributes.values',
                'brand',
                'activeVariants.attributeValues.attribute',
                'media',
            ])
            ->firstOrFail();
    }
}
