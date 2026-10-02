<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Actions\Catalog\CompareProductsAction;
use Reyhan\Core\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CompareProductsController extends Controller
{
    /**
     * Compare up to 4 products.
     */
    public function __invoke(Request $request, CompareProductsAction $action): JsonResponse
    {
        $validated = $request->validate([
            'products' => ['required', 'array', 'min:1', 'max:4'],
            'products.*' => ['required', 'string'],
        ]);

        $result = $action->execute($validated['products']);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
