<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $specification_group_id
 * @property string $name
 * @property string|null $unit
 * @property string $type
 * @property array<string, mixed>|null $options
 * @property bool $is_filterable
 * @property int $order
 * @property-read SpecificationGroup $group
 * @property-read Collection<int, Category> $categories
 * @property-read Collection<int, ProductSpecification> $productSpecifications
 */
#[Guarded(['id'])]
class Specification extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_filterable' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<SpecificationGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(SpecificationGroup::class, 'specification_group_id');
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_specifications');
    }

    /**
     * @return HasMany<ProductSpecification, $this>
     */
    public function productSpecifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class);
    }
}
