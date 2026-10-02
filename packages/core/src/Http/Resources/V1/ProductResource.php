<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Resources\V1;

use Reyhan\Core\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $primaryMedia = $this->getFirstMedia('gallery');
        $activeVariants = $this->activeVariants;
        $firstVariant = $activeVariants->first();
        $priceRange = $this->price_range;

        $hasDiscount = $activeVariants->contains(fn ($v) => $v->has_discount);
        $isInStock = $activeVariants->contains(fn ($v) => $v->stock > 0);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'thumbnail' => $primaryMedia?->getUrl(),
            'price_range' => $priceRange,
            'primary_price' => $firstVariant?->price,
            'primary_compare_at_price' => $firstVariant?->compare_at_price,
            'has_discount' => $hasDiscount,
            'is_in_stock' => $isInStock,
            'is_featured' => $this->is_featured,
            'brand' => $this->whenLoaded('brand', fn () => $this->brand ? new BrandResource($this->brand) : null),
            'category' => $this->whenLoaded('category', fn () => $this->category ? new CategoryResource($this->category) : null),
            'variants_count' => $activeVariants->count(),
        ];
    }
}
