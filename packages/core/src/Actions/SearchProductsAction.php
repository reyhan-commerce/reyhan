<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions;

use Reyhan\Core\Models\Product;
use Reyhan\Core\Pipelines\Normalizer\PersianNormalizer;
use Illuminate\Database\Eloquent\Builder;

final class SearchProductsAction
{
    /**
     * Apply typo-tolerant PostgreSQL pg_trgm fuzzy search and ILIKE matching on products.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function execute(Builder $query, ?string $term): Builder
    {
        if ($term === null || trim($term) === '') {
            return $query;
        }

        $clean = PersianNormalizer::normalizeSearchQuery($term);

        if ($clean === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($clean): void {
            $like = '%'.$clean.'%';

            $q->whereRaw('name % ?', [$clean])
                ->orWhereRaw('name ILIKE ?', [$like])
                ->orWhereHas('brand', function (Builder $b) use ($like, $clean): void {
                    $b->where('name', 'ILIKE', $like)
                        ->orWhere('name_en', 'ILIKE', $like)
                        ->orWhereRaw('name % ?', [$clean]);
                });
        })->orderByRaw('similarity(name, ?) DESC', [$clean]);
    }
}
