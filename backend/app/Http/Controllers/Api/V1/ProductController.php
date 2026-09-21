<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Catalog\ListProductsRequest;
use App\Http\Resources\V1\ProductDetailResource;
use App\Http\Resources\V1\ProductResource;
use App\Services\Catalog\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService,
    ) {}

    /**
     * List products with multi-dimensional filtering, search, and sorting.
     */
    public function index(ListProductsRequest $request): JsonResponse
    {
        /** @var array<string, mixed> $filters */
        $filters = $request->validated();
        $perPage = (int) $request->input('per_page', 15);

        $products = $this->productService->list($filters, $perPage);

        return ProductResource::collection($products)
            ->additional(['success' => true])
            ->response();
    }

    /**
     * Show single product by decoded slug with variant matrix and gallery.
     */
    public function show(string $product): JsonResponse
    {
        $model = $this->productService->findBySlug($product);

        return response()->json([
            'success' => true,
            'data' => new ProductDetailResource($model),
        ]);
    }
}
