<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Contracts\Models\CategoryContract;
use Reyhan\Core\Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property bool $is_active
 * @property int $order
 * @property-read Category|null $parent
 * @property-read EloquentCollection<int, Category> $children
 * @property-read EloquentCollection<int, Product> $products
 * @property-read EloquentCollection<int, Attribute> $attributes
 */
#[RouteKey('slug')]
#[Guarded(['id'])]
class Category extends Model implements CategoryContract, HasMedia
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasSlug, InteractsWithMedia;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
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
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return BelongsToMany<Attribute, $this>
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'category_attributes')
            ->withPivot(['is_variant_maker', 'is_filterable', 'order'])
            ->orderByPivot('order');
    }

    /**
     * @return BelongsToMany<Specification, $this>
     */
    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(Specification::class, 'category_specifications')
            ->with('group')
            ->orderBy('order');
    }

    /**
     * Get all ancestors (breadcrumb path) from root to parent.
     *
     * @return Collection<int, Category>
     */
    public function getAncestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Get all descendant category IDs recursively.
     *
     * @return Collection<int, int>
     */
    public function getDescendantIds(): Collection
    {
        $ids = collect();

        foreach ($this->children as $child) {
            $ids->push($child->id);
            $ids = $ids->merge($child->getDescendantIds());
        }

        return $ids;
    }

    /**
     * Get flat associative array of categories formatted as tree for Select dropdowns.
     *
     * @return array<int, string>
     */
    public static function treeOptions(?int $excludeId = null): array
    {
        $all = self::query()->orderBy('order')->orderBy('name')->get();
        $excludedIds = collect();

        if ($excludeId !== null) {
            $excludeRecord = $all->firstWhere('id', $excludeId);
            if ($excludeRecord) {
                $excludedIds = $excludeRecord->getDescendantIds()->push($excludeId);
            }
        }

        $options = [];
        $byParent = $all->groupBy('parent_id');

        $traverse = function (?int $parentId, string $prefix = '') use (&$traverse, &$options, $byParent, $excludedIds): void {
            $children = $byParent->get($parentId, collect());

            foreach ($children as $child) {
                if ($excludedIds->contains($child->id)) {
                    continue;
                }

                $options[$child->id] = $prefix ? $prefix.' '.$child->name : $child->name;
                $traverse($child->id, $prefix ? $prefix.'↳ ' : '↳ ');
            }
        };

        $traverse(null);

        return $options;
    }

    /**
     * @param  Builder<Category>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Category>  $query
     */
    #[Scope]
    protected function root(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /**
     * @param  Builder<Category>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function determineTitleColumnName(): string
    {
        return 'name';
    }

    public function determineParentColumnName(): string
    {
        return 'parent_id';
    }

    public function determineOrderColumnName(): string
    {
        return 'order';
    }
}
