<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property int $province_id
 * @property int $city_id
 * @property string $recipient_name
 * @property string $recipient_mobile
 * @property string $postal_code
 * @property string $address_line
 * @property string|null $building_number
 * @property string|null $unit
 * @property bool $is_default
 * @property-read string $full_address
 * @property-read Province|null $province
 * @property-read City|null $city
 */
#[Guarded(['id'])]
class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
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
     * @return BelongsTo<Province, $this>
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * @param  Builder<Address>  $query
     */
    #[Scope]
    protected function default(Builder $query): void
    {
        $query->where('is_default', true);
    }

    /**
     * Format full human-readable address.
     *
     * @return Attribute<string, never>
     */
    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $parts = [
                    $this->province?->name,
                    $this->city?->name,
                    $this->address_line,
                ];

                if ($this->building_number) {
                    $parts[] = __('No. :number', ['number' => $this->building_number]);
                }

                if ($this->unit) {
                    $parts[] = __('Unit :unit', ['unit' => $this->unit]);
                }

                $separator = app()->getLocale() === 'fa' ? '، ' : ', ';

                return implode($separator, array_filter($parts));
            }
        );
    }
}
