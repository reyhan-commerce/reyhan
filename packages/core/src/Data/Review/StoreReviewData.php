<?php

declare(strict_types=1);

namespace Reyhan\Core\Data\Review;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

final class StoreReviewData extends Data
{
    /**
     * @param  array<string, int>|null  $criteriaRatings
     * @param  array<int, string>|null  $strengths
     * @param  array<int, string>|null  $weaknesses
     */
    public function __construct(
        public int $rating,
        public string $comment,
        #[MapInputName('criteria_ratings')]
        public ?array $criteriaRatings = null,
        #[MapInputName('longevity_rating')]
        public ?int $longevityRating = null,
        #[MapInputName('coverage_rating')]
        public ?int $coverageRating = null,
        #[MapInputName('value_rating')]
        public ?int $valueRating = null,
        public ?array $strengths = null,
        public ?array $weaknesses = null,
    ) {}
}
