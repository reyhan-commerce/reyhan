<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Contracts\Models\ProductContract;
use Reyhan\Core\Enums\ReviewStatus;
use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Carbon\Carbon;
use Reyhan\Core\Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute as CastAttribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rankbeam\Seo\Traits\HasSEO;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property int|null $category_id
 * @property int|null $brand_id
 * @property string $name
 * @property string $slug
 * @property string|null $sku
 * @property string|null $description
 * @property string|null $short_description
 * @property bool $is_active
 * @property bool $is_featured
 * @property Carbon|null $published_at
 * @property-read Category|null $category
 * @property-read Brand|null $brand
 * @property-read Collection<int, ProductVariant> $variants
 * @property-read Collection<int, ProductVariant> $activeVariants
 * @property-read Collection<int, Review> $reviews
 * @property-read Collection<int, Review> $approvedReviews
 * @property-read Collection<int, Wishlist> $wishlists
 * @property-read Collection<int, OrderItem> $orderItems
 * @property-read array{min: int|null, max: int|null} $price_range
 */
#[RouteKey('slug')]
#[Guarded(['id'])]
class Product extends Model implements HasMedia, ProductContract, ProvidesActivityTitle
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasSEO, HasSlug, InteractsWithMedia, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function activityTitle(): ?string
    {
        return $this->name;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_tax_exempt' => 'boolean',
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

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('order');
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @param  Builder<Product>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * @param  Builder<Product>  $query
     */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * @param  Builder<Product>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')
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

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', ReviewStatus::Approved)->latest();
    }

    /**
     * @return HasMany<Wishlist, $this>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * @return HasMany<ProductQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class)->latest();
    }

    /**
     * @return HasMany<ProductPriceHistory, $this>
     */
    public function priceHistories(): HasMany
    {
        return $this->hasMany(ProductPriceHistory::class)->orderBy('recorded_at', 'asc');
    }

    /**
     * @return HasMany<ProductSpecification, $this>
     */
    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class);
    }

    /**
     * Get specifications grouped by specification group.
     *
     * @return list<array{group_id: int, group_name: string, items: list<array{id: int, name: string, value: string, unit: string|null}>}>
     */
    public function specificationsGrouped(): array
    {
        $specs = $this->specifications()
            ->with(['specification.group'])
            ->get();

        $groups = [];

        foreach ($specs as $prodSpec) {
            $spec = $prodSpec->specification;
            $groupId = $spec->group->id;
            if (! isset($groups[$groupId])) {
                $groups[$groupId] = [
                    'group_id' => $groupId,
                    'group_name' => $spec->group->name,
                    'order' => $spec->group->order,
                    'items' => [],
                ];
            }

            $groups[$groupId]['items'][] = [
                'id' => $spec->id,
                'name' => $spec->name,
                'value' => $prodSpec->value,
                'unit' => $spec->unit,
            ];
        }

        uasort($groups, fn ($a, $b) => $a['order'] <=> $b['order']);

        return array_values(array_map(function ($g) {
            unset($g['order']);

            return $g;
        }, $groups));
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
                'criteria_averages' => [],
                'total_reviews' => 0,
            ];
        }

        /** @var Collection<int, Review> $allReviews */
        $allReviews = $approved->get();
        $criteriaSums = [];
        $criteriaCounts = [];

        foreach ($allReviews as $rev) {
            if (is_array($rev->criteria_ratings)) {
                foreach ($rev->criteria_ratings as $criterion => $val) {
                    $criteriaSums[$criterion] = ($criteriaSums[$criterion] ?? 0) + (float) $val;
                    $criteriaCounts[$criterion] = ($criteriaCounts[$criterion] ?? 0) + 1;
                }
            }
        }

        $criteriaAverages = [];
        foreach ($criteriaSums as $criterion => $sum) {
            $criteriaAverages[$criterion] = round($sum / $criteriaCounts[$criterion], 1);
        }

        return [
            'average_rating' => round((float) $approved->avg('rating'), 1),
            'average_longevity' => round((float) ($approved->avg('longevity_rating') ?: ($criteriaAverages['longevity'] ?? 5.0)), 1),
            'average_coverage' => round((float) ($approved->avg('coverage_rating') ?: ($criteriaAverages['coverage'] ?? 5.0)), 1),
            'average_value' => round((float) ($approved->avg('value_rating') ?: ($criteriaAverages['value'] ?? 5.0)), 1),
            'criteria_averages' => $criteriaAverages,
            'total_reviews' => $count,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
    }
}
