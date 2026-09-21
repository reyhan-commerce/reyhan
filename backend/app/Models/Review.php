<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'longevity_rating',
        'coverage_rating',
        'value_rating',
        'comment',
        'strengths',
        'weaknesses',
        'is_verified_purchase',
        'status',
        'admin_reply',
        'admin_reply_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::Approved);
    }
}
