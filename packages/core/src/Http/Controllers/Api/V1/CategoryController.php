<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Resources\V1\CategoryResource;
use Reyhan\Core\Services\Catalog\CategoryService;
use Illuminate\Http\JsonResponse;

final class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService,
    ) {}

    /**
     * List paginated active categories.
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryService->listActive();

        return CategoryResource::collection($categories)
            ->additional(['success' => true])
            ->response();
    }

    /**
     * Show single category by slug with children and permitted attributes.
     */
    public function show(string $category): JsonResponse
    {
        $model = $this->categoryService->findBySlug($category);

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($model),
        ]);
    }
}
