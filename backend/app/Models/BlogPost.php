<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\BlogPostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $category_id
 * @property int|null $author_id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string $content
 * @property string|null $featured_image
 * @property int $reading_time
 * @property int $views_count
 * @property bool $is_featured
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array<string>|null $tags
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property-read BlogCategory $category
 * @property-read Admin|null $author
 */
class BlogPost extends Model
{
    /** @use HasFactory<BlogPostFactory> */
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

    /**
     * @return BelongsTo<BlogCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function calculateReadingTime(): int
    {
        $words = preg_split('/\s+/u', strip_tags((string) $this->content), -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = is_array($words) ? count($words) : 0;

        return max(1, (int) ceil($wordCount / 180));
    }
}
