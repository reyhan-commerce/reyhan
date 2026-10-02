<?php

declare(strict_types=1);

namespace Reyhan\Core\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Domain Contract for Reyhan Category Entity.
 */
interface CategoryContract
{
    /**
     * Relationship to parent category.
     */
    public function parent(): BelongsTo;

    /**
     * Relationship to direct child categories.
     */
    public function children(): HasMany;

    /**
     * Relationship to products under this category.
     */
    public function products(): HasMany;

    /**
     * Relationship to category specifications / attributes.
     */
    public function attributes(): BelongsToMany;
}
