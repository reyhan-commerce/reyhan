<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute as CastAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model implements HasMedia
{
    use HasSlug;
    use InteractsWithMedia;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'description',
        'short_description',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(255);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('order');
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * @return CastAttribute<array{min: int|null, max: int|null}, never>
     */
    protected function priceRange(): CastAttribute
    {
        return CastAttribute::make(
            get: function (): array {
                $variants = $this->activeVariants;

                if ($variants->isEmpty()) {
                    return ['min' => null, 'max' => null];
                }

                return [
                    'min' => (int) $variants->min('price'),
                    'max' => (int) $variants->max('price'),
                ];
            },
        );
    }

    /**
     * Build the available variants matrix scoped to this product's category attributes.
     *
     * @return list<array{attribute: array{id: int, name: string, slug: string, type: string}, values: list<array{id: int, value: string, label: string|null, hex_code: string|null, available: bool}>}>
     */
    public function availableVariantsMatrix(): array
    {
        $category = $this->category;

        if (! $category) {
            return [];
        }

        // Get variant-maker attributes for this category
        $variantAttributes = $category->attributes()
            ->wherePivot('is_variant_maker', true)
            ->with('values')
            ->get();

        // Get all active variant attribute value IDs
        $activeVariants = $this->activeVariants()
            ->with('attributeValues')
            ->get();

        // Build a set of attribute_value_ids that exist in active, in-stock variants
        $availableValueIds = $activeVariants
            ->flatMap(fn (ProductVariant $v) => $v->attributeValues->pluck('id'))
            ->unique()
            ->toArray();

        $inStockValueIds = $activeVariants
            ->filter(fn (ProductVariant $v) => $v->stock > 0)
            ->flatMap(fn (ProductVariant $v) => $v->attributeValues->pluck('id'))
            ->unique()
            ->toArray();

        $matrix = [];

        foreach ($variantAttributes as $attribute) {
            $values = [];

            foreach ($attribute->values as $value) {
                // Only include values that are actually used by at least one variant
                if (! in_array($value->id, $availableValueIds, true)) {
                    continue;
                }

                $values[] = [
                    'id' => $value->id,
                    'value' => $value->value,
                    'label' => $value->label,
                    'hex_code' => $value->hex_code,
                    'available' => in_array($value->id, $inStockValueIds, true),
                ];
            }

            if (! empty($values)) {
                $matrix[] = [
                    'attribute' => [
                        'id' => $attribute->id,
                        'name' => $attribute->name,
                        'slug' => $attribute->slug,
                        'type' => $attribute->type->value,
                    ],
                    'values' => $values,
                ];
            }
        }

        return $matrix;
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', \App\Enums\ReviewStatus::Approved)->latest();
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get aggregated review stats for this product.
     *
     * @return array{average_rating: float, average_longevity: float, average_coverage: float, average_value: float, total_reviews: int}
     */
    public function getReviewStats(): array
    {
        $approved = $this->approvedReviews();
        $count = $approved->count();

        if ($count === 0) {
            return [
                'average_rating' => 5.0,
                'average_longevity' => 5.0,
                'average_coverage' => 5.0,
                'average_value' => 5.0,
                'total_reviews' => 0,
            ];
        }

        return [
            'average_rating' => round((float) $approved->avg('rating'), 1),
            'average_longevity' => round((float) $approved->avg('longevity_rating'), 1),
            'average_coverage' => round((float) $approved->avg('coverage_rating'), 1),
            'average_value' => round((float) $approved->avg('value_rating'), 1),
            'total_reviews' => $count,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
    }
}
