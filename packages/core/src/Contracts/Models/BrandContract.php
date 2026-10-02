<?php

declare(strict_types=1);

namespace Reyhan\Core\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Domain Contract for Reyhan Brand Entity.
 */
interface BrandContract
{
    /**
     * Relationship to products under this brand.
     */
    public function products(): HasMany;
}
