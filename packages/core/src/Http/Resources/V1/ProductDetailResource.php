<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Resources\V1;

use Reyhan\Core\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $gallery = $this->getMedia('gallery')->map(fn ($media) => [
            'id' => $media->id,
            'url' => $media->getUrl(),
            'name' => $media->name,
            'file_name' => $media->file_name,
        ]);

        $breadcrumbs = collect();
        if ($this->category) {
            $ancestors = $this->category->getAncestors();
            foreach ($ancestors as $ancestor) {
                $breadcrumbs->push([
                    'id' => $ancestor->id,
                    'name' => $ancestor->name,
                    'slug' => $ancestor->slug,
                ]);
            }
            $breadcrumbs->push([
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'meta_title' => $this->meta_title ?? $this->name,
            'meta_description' => $this->meta_description ?? $this->short_description,
            'gallery' => $gallery,
            'price_range' => $this->price_range,
            'is_featured' => $this->is_featured,
            'brand' => $this->whenLoaded('brand', fn () => $this->brand ? new BrandResource($this->brand) : null),
            'category' => $this->whenLoaded('category', fn () => $this->category ? new CategoryResource($this->category) : null),
            'breadcrumbs' => $breadcrumbs,
            'variants' => ProductVariantResource::collection($this->whenLoaded('activeVariants')),
            'variants_matrix' => $this->availableVariantsMatrix(),
            'specifications' => $this->specificationsGrouped(),
        ];
    }
}
