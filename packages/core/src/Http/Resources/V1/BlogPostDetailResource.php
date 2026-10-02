<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Resources\V1;

use Reyhan\Core\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BlogPost
 */
class BlogPostDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'content' => $this->content,
            'featured_image' => $this->featured_image ? (str_starts_with($this->featured_image, 'http') ? $this->featured_image : asset('storage/'.$this->featured_image)) : null,
            'reading_time' => $this->reading_time ?: $this->calculateReadingTime(),
            'views_count' => $this->views_count,
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'tags' => $this->tags ?? [],
            'meta_title' => $this->meta_title ?? $this->title,
            'meta_description' => $this->meta_description ?? $this->summary,
            'category' => $this->whenLoaded('category', fn () => new BlogCategoryResource($this->category)),
            'author' => $this->author ? [
                'name' => $this->author->name,
                'avatar' => $this->author->avatar ? asset('storage/'.$this->author->avatar) : null,
            ] : [
                'name' => __('Editorial Team & Beauty Experts'),
                'avatar' => null,
            ],
        ];
    }
}
