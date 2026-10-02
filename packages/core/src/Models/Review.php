<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Enums\ReviewStatus;
use Carbon\Carbon;
use Reyhan\Core\Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int $rating
 * @property array<string, mixed>|null $criteria_ratings
 * @property int|null $longevity_rating
 * @property int|null $coverage_rating
 * @property int|null $value_rating
 * @property string|null $title
 * @property string|null $comment
 * @property array<int, string>|null $strengths
 * @property array<int, string>|null $weaknesses
 * @property bool $is_verified_purchase
 * @property ReviewStatus $status
 * @property string|null $admin_reply
 * @property Carbon|null $admin_reply_at
 * @property-read User|null $user
 * @property-read Product|null $product
 */
#[Guarded(['id'])]
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'criteria_ratings' => 'array',
            'longevity_rating' => 'integer',
            'coverage_rating' => 'integer',
            'value_rating' => 'integer',
            'strengths' => 'array',
            'weaknesses' => 'array',
            'is_verified_purchase' => 'boolean',
            'status' => ReviewStatus::class,
            'admin_reply_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @param  Builder<Review>  $query
     */
    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('status', ReviewStatus::Approved);
    }
}
