<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Enums\BannerPosition;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string $title
 * @property string|null $subtitle
 * @property string $image_url
 * @property string|null $mobile_image_url
 * @property string|null $link_url
 * @property BannerPosition $position
 * @property int $order
 * @property bool $is_active
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Guarded(['id'])]
final class Banner extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->useDisk('public')->singleFile();
        $this->addMediaCollection('mobile_image')->useDisk('public')->singleFile();
    }

    /**
     * @return Attribute<string, void>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): string {
                $url = $this->getFirstMediaUrl('image') ?: ($value ?? '');
                if ($url === '') {
                    return '';
                }

                return str_starts_with($url, 'http://') || str_starts_with($url, 'https://')
                    ? $url
                    : url($url);
            }
        );
    }

    /**
     * @return Attribute<string|null, void>
     */
    protected function mobileImageUrl(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                $url = $this->getFirstMediaUrl('mobile_image') ?: $value;
                if (! $url) {
                    return null;
                }

                return str_starts_with($url, 'http://') || str_starts_with($url, 'https://')
                    ? $url
                    : url($url);
            }
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => BannerPosition::class,
            'order' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('order')
            ->orderByDesc('id');
    }
}
