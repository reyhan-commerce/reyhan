<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rankbeam\Seo\Traits\HasSEO;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $content
 * @property array<string, mixed>|null $metadata
 * @property bool $is_active
 */
#[Guarded(['id'])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, HasSEO;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Page>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
