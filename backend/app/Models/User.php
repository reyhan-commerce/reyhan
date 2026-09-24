<?php

declare(strict_types=1);

namespace App\Models;

use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $national_code
 * @property string $mobile
 * @property string|null $email
 * @property string|null $avatar
 * @property bool $is_active
 * @property Carbon|null $mobile_verified_at
 * @property-read int $loyalty_points_balance
 * @property-read array{key: string, label: string, color: string, icon: string, min_points: int, next_points: ?int, discount_percent: int} $loyalty_tier
 * @property-read string $full_name
 */
class User extends Authenticatable implements ProvidesActivityTitle
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, LogsActivity, Notifiable, SoftDeletes;

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logExcept(['created_at', 'updated_at', 'remember_token'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function activityTitle(): ?string
    {
        return $this->full_name ?: $this->mobile;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Route notifications for the SMS channel.
     */
    public function routeNotificationForSms(): string
    {
        return $this->mobile;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'mobile_verified_at' => 'datetime',
        ];
    }

    /**
     * Accessor for full name.
     *
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $parts = array_filter([$this->first_name, $this->last_name]);

                return empty($parts) ? __('Dear User') : implode(' ', $parts);
            }
        );
    }

    /**
     * @return HasMany<Address, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * @return HasOne<Address, $this>
     */
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    /**
     * @return HasMany<Wishlist, $this>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function wishlistProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    /**
     * @return HasMany<LoyaltyTransaction, $this>
     */
    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class)->latest();
    }

    /**
     * @return Attribute<int, never>
     */
    protected function loyaltyPointsBalance(): Attribute
    {
        return Attribute::make(
            get: fn (): int => (int) $this->loyaltyTransactions()->sum('points'),
        );
    }

    /**
     * @return Attribute<array{key: string, label: string, color: string, icon: string, min_points: int, next_points: ?int, discount_percent: int}, never>
     */
    protected function loyaltyTier(): Attribute
    {
        return Attribute::make(
            get: function (): array {
                $points = (int) $this->loyalty_points_balance;

                if ($points >= 1500) {
                    return [
                        'key' => 'gold',
                        'label' => __('Gold (VIP)'),
                        'color' => 'amber',
                        'icon' => 'i-lucide-crown',
                        'min_points' => 1500,
                        'next_points' => null,
                        'discount_percent' => 10,
                    ];
                }

                if ($points >= 500) {
                    return [
                        'key' => 'silver',
                        'label' => __('Silver'),
                        'color' => 'slate',
                        'icon' => 'i-lucide-award',
                        'min_points' => 500,
                        'next_points' => 1500,
                        'discount_percent' => 5,
                    ];
                }

                return [
                    'key' => 'bronze',
                    'label' => __('Bronze'),
                    'color' => 'orange',
                    'icon' => 'i-lucide-shield',
                    'min_points' => 0,
                    'next_points' => 500,
                    'discount_percent' => 0,
                ];
            }
        );
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function markMobileAsVerified(): self
    {
        if ($this->mobile_verified_at === null) {
            $this->forceFill(['mobile_verified_at' => now()])->save();
        }

        return $this;
    }

    public function awardLoyaltyPoints(int $points, string $type, string $description, ?string $referenceId = null): LoyaltyTransaction
    {
        return $this->loyaltyTransactions()->create([
            'points' => $points,
            'type' => $type,
            'description' => $description,
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Compatibility guard for FilamentShield's global Gate::before callback.
     * End-users do not have Spatie roles.
     *
     * @param  mixed  ...$args
     */
    public function hasRole(...$args): bool
    {
        return false;
    }
}
