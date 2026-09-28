<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Catalog\CreateStockAlertAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Catalog\StoreStockAlertRequest;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;

class StockAlertController extends Controller
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
