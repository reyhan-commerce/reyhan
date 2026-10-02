<?php

declare(strict_types=1);

namespace Reyhan\Core\Data\Catalog;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

final class ProductFilterData extends Data
{
    /**
     * @param  string|array<string>|null  $brand
     * @param  array<string, mixed>|null  $attributes
     */
    public function __construct(
        public ?string $category = null,
        public string|array|null $brand = null,
        #[MapInputName('min_price')]
        public int|string|null $minPrice = null,
        #[MapInputName('max_price')]
        public int|string|null $maxPrice = null,
        #[MapInputName('in_stock')]
        public bool|string|null $inStock = null,
        #[MapInputName('has_discount')]
        public bool|string|null $hasDiscount = null,
        public ?array $attributes = null,
        public ?string $search = null,
        public ?string $sort = null,
    ) {}
}
