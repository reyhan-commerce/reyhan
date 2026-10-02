<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Resources\V1;

use Reyhan\Core\Models\Cart;
use Reyhan\Core\Services\Pricing\PricingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cart
 */
class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var PricingService $pricingService */
        $pricingService = app(PricingService::class);
        $pricing = $pricingService->calculateCart($this->resource);

        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->with(['variant.product.media', 'variant.product.brand'])->get();

        return [
            'id' => $this->id,
            'session_id' => $this->session_id,
            'items_count' => $pricing->totalItemsCount,
            'items' => CartItemResource::collection($items),
            'pricing' => $pricing,
        ];
    }
}
