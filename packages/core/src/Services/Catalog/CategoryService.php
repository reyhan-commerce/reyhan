<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Catalog;

use Reyhan\Core\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    public const CACHE_KEY_TREE = 'catalog:categories:tree';

    public const CACHE_TTL = 86400; // 24 hours

    /**
     * Return hierarchical category tree, cached in Redis.
     *
     * @return Collection<int, Category>
     */
    public function getTree(): Collection
    {
        $cached = Cache::get(self::CACHE_KEY_TREE);
        if ($cached instanceof Collection) {
            return $cached;
        }

        $tree = Category::query()
            ->root()
            ->active()
            ->ordered()
            ->with([
                'children' => fn ($q) => $q->active()->ordered()->with([
                    'children' => fn ($subQ) => $subQ->active()->ordered(),
                ]),
            ])
            ->get();

        Cache::put(self::CACHE_KEY_TREE, $tree, self::CACHE_TTL);

        return $tree;
    }

    /**
     * Invalidate category tree cache.
     */
    public function forgetTreeCache(): void
    {
        Cache::forget(self::CACHE_KEY_TREE);
    }

    /**
     * Get paginated active categories.
     *
     * @return LengthAwarePaginator<int, Category>
     */
    public function listActive(int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()
            ->active()
            ->ordered()
            ->withCount('products')
            ->paginate($perPage);
    }

    /**
     * Find category by slug with relationships.
     */
    public function findBySlug(string $slug): Category
    {
        return Category::query()
            ->where('slug', $slug)
            ->active()
            ->with([
                'parent',
                'children' => fn ($q) => $q->active()->ordered(),
                'attributes.values',
            ])
            ->withCount('products')
            ->firstOrFail();
    }
}
