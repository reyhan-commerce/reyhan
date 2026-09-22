<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'summary',
        'content',
        'featured_image',
        'reading_time',
        'views_count',
        'is_featured',
        'is_published',
        'published_at',
        'tags',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'reading_time' => 'integer',
            'views_count' => 'integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'tags' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function calculateReadingTime(): int
    {
        $wordCount = mb_str_word_count(strip_tags((string) $this->content));
        return max(1, (int) ceil($wordCount / 180));
    }
}
