<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Resources\V1;

use Reyhan\Core\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductVariant
 */
class ProductVariantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'title' => $this->title,
            'price' => $this->price,
            'compare_at_price' => $this->compare_at_price,
            'has_discount' => $this->has_discount,
            'stock' => $this->stock,
            'is_in_stock' => $this->stock > 0,
            'is_low_stock' => $this->is_low_stock,
            'stock_status' => $this->stock_status->value,
            'weight' => $this->weight,
            'attributes' => $this->whenLoaded('attributeValues', function () {
                return $this->attributeValues->map(fn ($val) => [
                    'attribute_id' => $val->attribute_id,
                    'attribute_name' => $val->attribute?->name,
                    'attribute_slug' => $val->attribute?->slug,
                    'value_id' => $val->id,
                    'value' => $val->value,
                    'label' => $val->display_label,
                    'hex_code' => $val->hex_code,
                ]);
            }),
        ];
    }
}
