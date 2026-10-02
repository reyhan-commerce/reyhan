<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Actions\Catalog\CreateStockAlertAction;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Requests\Api\V1\Catalog\StoreStockAlertRequest;
use Reyhan\Core\Models\ProductVariant;
use Illuminate\Http\JsonResponse;

final class StockAlertController extends Controller
{
    /**
     * Subscribe to stock alert for a variant.
     */
    public function store(StoreStockAlertRequest $request, CreateStockAlertAction $action): JsonResponse
    {
        /** @var ProductVariant $variant */
        $variant = ProductVariant::query()->findOrFail($request->validated('variant_id'));

        $user = $request->user('sanctum');

        $alert = $action->execute(
            variant: $variant,
            mobile: (string) $request->validated('mobile'),
            user: $user,
        );

        return response()->json([
            'success' => true,
            'message' => __('You will be notified via SMS as soon as this item is restocked.'),
            'data' => [
                'id' => $alert->id,
                'status' => $alert->status,
            ],
        ]);
    }
}
