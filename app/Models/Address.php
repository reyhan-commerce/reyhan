<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'province_id',
        'city_id',
        'recipient_name',
        'recipient_mobile',
        'postal_code',
        'address_line',
        'building_number',
        'unit',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Format full human-readable address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [
            $this->province?->name,
            $this->city?->name,
            $this->address_line,
        ];

        if ($this->building_number) {
            $parts[] = "پلاک {$this->building_number}";
        }

        if ($this->unit) {
            $parts[] = "واحد {$this->unit}";
        }

        return implode('، ', array_filter($parts));
    }
}
