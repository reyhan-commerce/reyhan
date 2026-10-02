<?php

declare(strict_types=1);

namespace Reyhan\Core\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Domain Contract for Reyhan Product Entity.
 */
interface ProductContract
{
    /**
     * Relationship to the product category.
     */
    public function category(): BelongsTo;

    /**
     * Relationship to the product brand.
     */
    public function brand(): BelongsTo;

    /**
     * Relationship to product variants.
     */
    public function variants(): HasMany;

    /**
     * Relationship to active product variants.
     */
    public function activeVariants(): HasMany;

    /**
     * Relationship to product reviews.
     */
    public function reviews(): HasMany;

    /**
     * Relationship to approved product reviews.
     */
    public function approvedReviews(): HasMany;

    /**
     * Relationship to product specifications.
     */
    public function specifications(): HasMany;

    /**
     * Relationship to ordered items.
     */
    public function orderItems(): HasMany;

    /**
     * Build the available variants matrix scoped to this product's category attributes.
     *
     * @return list<array{attribute: array{id: int, name: string, slug: string, type: string}, values: list<array{id: int, value: string, label: string|null, hex_code: string|null, available: bool}>}>
     */
    public function availableVariantsMatrix(): array;

    /**
     * Get aggregated review stats for this product.
     *
     * @return array{average_rating: float, average_longevity: float, average_coverage: float, average_value: float, criteria_averages: array<string, float>, total_reviews: int}
     */
    public function getReviewStats(): array;
}
