<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Resources\V1;

use Reyhan\Core\Models\Attribute;
use Reyhan\Core\Models\AttributeValue;
use Reyhan\Core\Models\Category;
use Illuminate\Database\Eloquent\Relations\Pivot;
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
                return $this->attributes->map(function (Attribute $attr): array {
                    /** @var Pivot|null $pivot */
                    $pivot = $attr->pivot;

                    return [
                        'id' => $attr->id,
                        'name' => $attr->name,
                        'slug' => $attr->slug,
                        'type' => $attr->type->value,
                        'is_variant_maker' => (bool) ($pivot?->getAttribute('is_variant_maker') ?? false),
                        'is_filterable' => (bool) ($pivot?->getAttribute('is_filterable') ?? false),
                        'values' => $attr->values->map(fn (AttributeValue $val): array => [
                            'id' => $val->id,
                            'value' => $val->value,
                            'label' => $val->display_label,
                            'hex_code' => $val->hex_code,
                        ]),
                    ];
                });
            }),
        ];
    }
}
