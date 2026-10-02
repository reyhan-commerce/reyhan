<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Data\Catalog\ProductFilterData;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Requests\Api\V1\Catalog\ListProductsRequest;
use Reyhan\Core\Http\Resources\V1\ProductDetailResource;
use Reyhan\Core\Http\Resources\V1\ProductResource;
use Reyhan\Core\Services\Catalog\ProductService;
use Illuminate\Http\JsonResponse;

final class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService,
    ) {}

    /**
     * List products with multi-dimensional filtering, search, and sorting.
     */
    public function index(ListProductsRequest $request): JsonResponse
    {
        $filters = ProductFilterData::from($request->validated());
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

    /**
     * Show related products for PDP.
     */
    public function related(string $product): JsonResponse
    {
        $model = $this->productService->findBySlug($product);
        $related = $this->productService->getRelatedProducts($model);

        return ProductResource::collection($related)
            ->additional(['success' => true])
            ->response();
    }
}
