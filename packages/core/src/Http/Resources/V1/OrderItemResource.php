<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Resources\V1;

use Reyhan\Core\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */
class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'product_name' => $this->product_name,
            'variant_title' => $this->variant_title,
            'sku' => $this->sku,
            'unit_price' => $this->unit_price,
            'discount_amount' => $this->discount_amount,
            'final_price' => $this->final_price,
            'quantity' => $this->quantity,
            'total_price' => $this->total_price,
            'attributes' => $this->attributes_snapshot ?? [],
            'thumbnail' => $this->product?->getFirstMediaUrl('gallery'),
        ];
    }
}
