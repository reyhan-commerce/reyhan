<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'image' => $this->getFirstMediaUrl('image') ?: $this->image,
            'order' => $this->order,
            'products_count' => $this->whenCounted('products'),
            'parent' => $this->whenLoaded('parent', fn () => $this->parent ? new self($this->parent) : null),
            'children' => self::collection($this->whenLoaded('children')),
            'attributes' => $this->whenLoaded('attributes', function () {
                return $this->attributes->map(fn ($attr) => [
                    'id' => $attr->id,
                    'name' => $attr->name,
                    'slug' => $attr->slug,
                    'type' => $attr->type->value,
                    'is_variant_maker' => (bool) $attr->pivot->is_variant_maker,
                    'is_filterable' => (bool) $attr->pivot->is_filterable,
                    'values' => $attr->values->map(fn ($val) => [
                        'id' => $val->id,
                        'value' => $val->value,
                        'label' => $val->display_label,
                        'hex_code' => $val->hex_code,
                    ]),
                ]);
            }),
        ];
    }
}
