<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Catalog;

use Reyhan\Core\Actions\SearchProductsAction;
use Reyhan\Core\Data\Catalog\ProductFilterData;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ProductService
{
    public function __construct(
        protected SearchProductsAction $searchAction,
    ) {}

    /**
     * List products with multidimensional filtering, search, and sorting.
     *
     * @param  ProductFilterData|array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function list(ProductFilterData|array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $data = $filters instanceof ProductFilterData ? $filters : ProductFilterData::from($filters);

        $query = Product::query()
            ->active()
            ->with([
                'category',
                'brand',
                'activeVariants.attributeValues',
                'media',
            ]);

        // 1. Search term (pg_trgm fuzzy search)
        if (! empty($data->search)) {
            $query = $this->searchAction->execute($query, $data->search);
        }

        // 2. Category filter (including child categories)
        if (! empty($data->category)) {
            $categorySlug = $data->category;
            $category = Category::query()->where('slug', $categorySlug)->first();

            if ($category) {
                $categoryIds = $category->children()->pluck('id')->push($category->id)->all();
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // 3. Brand filter (single or array of slugs)
        if (! empty($data->brand)) {
            $brands = is_array($data->brand) ? $data->brand : explode(',', (string) $data->brand);
            $query->whereHas('brand', function (Builder $b) use ($brands): void {
                $b->whereIn('slug', $brands);
            });
        }

        // 4. Price range filter (on variants)
        if (is_numeric($data->minPrice)) {
            $minPrice = (int) $data->minPrice;
            $query->whereHas('activeVariants', function (Builder $v) use ($minPrice): void {
                $v->where('price', '>=', $minPrice);
            });
        }

        if (is_numeric($data->maxPrice)) {
            $maxPrice = (int) $data->maxPrice;
            $query->whereHas('activeVariants', function (Builder $v) use ($maxPrice): void {
                $v->where('price', '<=', $maxPrice);
            });
        }

        // 5. In-stock toggle
        if (! empty($data->inStock) && filter_var($data->inStock, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas('activeVariants', function (Builder $v): void {
                $v->where('stock', '>', 0);
            });
        }

        // 6. Has discount toggle
        if (! empty($data->hasDiscount) && filter_var($data->hasDiscount, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas('activeVariants', function (Builder $v): void {
                $v->whereNotNull('compare_at_price')
                    ->whereColumn('compare_at_price', '>', 'price');
            });
        }

        // 7. Dynamic Attribute values filter
        if (! empty($data->attributes)) {
            foreach ($data->attributes as $attrSlug => $values) {
                if (empty($values)) {
                    continue;
                }
                $valList = is_array($values) ? $values : explode(',', (string) $values);
                $query->whereHas('activeVariants.attributeValues', function (Builder $av) use ($attrSlug, $valList): void {
                    $av->whereIn('value', $valList)
                        ->orWhereIn('id', array_filter($valList, 'is_numeric'))
                        ->whereHas('attribute', fn (Builder $a) => $a->where('slug', $attrSlug));
                });
            }
        }

        // 8. Sorting (when not sorted by search similarity)
        if (empty($data->search)) {
            $sort = $data->sort ?? 'latest';
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
                        ->orderByDesc('price')
                        ->limit(1),
                    'desc'
                ),
                'featured' => $query->orderByDesc('is_featured')->latest(),
                'bestselling', 'popular' => $query->withSum('orderItems', 'quantity')
                    ->orderByDesc('order_items_sum_quantity')
                    ->latest(),
                default => $query->latest(),
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
                'specifications.specification.group',
                'media',
            ])
            ->firstOrFail();
    }

    /**
     * Get active related products sharing same category or brand.
     *
     * @return Collection<int, Product>
     */
    public function getRelatedProducts(Product $product, int $limit = 8): Collection
    {
        return Product::query()
            ->active()
            ->where('id', '!=', $product->id)
            ->where(function (Builder $q) use ($product): void {
                if ($product->category_id) {
                    $q->where('category_id', $product->category_id);
                }
                if ($product->brand_id) {
                    $q->orWhere('brand_id', $product->brand_id);
                }
            })
            ->with([
                'category',
                'brand',
                'activeVariants.attributeValues',
                'media',
            ])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
