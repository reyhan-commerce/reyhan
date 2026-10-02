<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Resources\V1;

use Reyhan\Core\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CartItem
 */
class CartItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $variant = $this->variant;
        $product = $variant?->product;
        $primaryMedia = $product?->getFirstMedia('gallery');

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'unit_price' => $variant->price ?? 0,
            'subtotal' => $this->subtotal,
            'original_subtotal' => $this->original_subtotal,
            'discount_amount' => max(0, $this->original_subtotal - $this->subtotal),
            'variant' => $variant ? [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'title' => $variant->title,
                'price' => $variant->price,
                'compare_at_price' => $variant->compare_at_price,
                'stock' => $variant->stock,
                'is_in_stock' => $variant->stock > 0,
                'is_low_stock' => $variant->is_low_stock,
                'product' => $product ? [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'thumbnail' => $primaryMedia?->getUrl(),
                    'brand' => $product->brand?->name,
                ] : null,
            ] : null,
        ];
    }
}
