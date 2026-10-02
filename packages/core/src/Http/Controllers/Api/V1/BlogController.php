<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Resources\V1\BlogCategoryResource;
use Reyhan\Core\Http\Resources\V1\BlogPostDetailResource;
use Reyhan\Core\Http\Resources\V1\BlogPostResource;
use Reyhan\Core\Models\BlogCategory;
use Reyhan\Core\Models\BlogPost;
use Reyhan\Core\Pipelines\Normalizer\PersianNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BlogController extends Controller
{
    /**
     * Get active blog categories.
     */
    public function categories(): JsonResponse
    {
        $categories = BlogCategory::query()
            ->active()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => BlogCategoryResource::collection($categories),
        ]);
    }

    /**
     * Get paginated blog posts with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::query()
            ->published()
            ->with(['category', 'author']);

        if ($categorySlug = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        if ($search = $request->query('search')) {
            $cleanSearch = PersianNormalizer::normalizeSearchQuery((string) $search);
            $query->where(function ($q) use ($cleanSearch) {
                $q->where('title', 'like', "%{$cleanSearch}%")
                    ->orWhere('summary', 'like', "%{$cleanSearch}%")
                    ->orWhere('content', 'like', "%{$cleanSearch}%");
            });
        }

        if ($tag = $request->query('tag')) {
            $query->whereJsonContains('tags', $tag);
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        $sort = $request->query('sort', 'latest');
        match ($sort) {
            'popular' => $query->orderByDesc('views_count'),
            'oldest' => $query->orderBy('published_at')->orderBy('id'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };

        $perPage = min(max((int) $request->query('per_page', 9), 1), 30);
        $posts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => BlogPostResource::collection($posts->items()),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Get single blog post by slug with view increment and related posts.
     */
    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::query()
            ->published()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment views count atomically
        $post->increment('views_count');

        // Fetch related posts in same category
        $relatedPosts = BlogPost::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn ($q) => $q->where('category_id', $post->category_id))
            ->orderByDesc('published_at')
            ->limit(3)
            ->with('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => new BlogPostDetailResource($post),
            'related' => BlogPostResource::collection($relatedPosts),
        ]);
    }

    /**
     * Get top featured blog posts.
     */
    public function featured(): JsonResponse
    {
        $featuredPosts = BlogPost::query()
            ->published()
            ->featured()
            ->with(['category', 'author'])
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return response()->json([
            'success' => true,
            'data' => BlogPostResource::collection($featuredPosts),
        ]);
    }
}
