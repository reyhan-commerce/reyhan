<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Resources\V1\CategoryTreeResource;
use Reyhan\Core\Services\Catalog\CategoryService;
use Illuminate\Http\JsonResponse;

final class CategoryTreeController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService,
    ) {}

    /**
     * Get Redis-cached hierarchical category tree.
     */
    public function __invoke(): JsonResponse
    {
        $tree = $this->categoryService->getTree();

        return response()->json([
            'success' => true,
            'data' => CategoryTreeResource::collection($tree),
        ]);
    }
}
