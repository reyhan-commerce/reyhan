<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryTreeResource;
use App\Services\Catalog\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryTreeController extends Controller
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
