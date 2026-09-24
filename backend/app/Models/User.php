<?php

declare(strict_types=1);

namespace App\Models;

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

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'national_code',
        'mobile',
        'email',
        'avatar',
        'is_active',
        'mobile_verified_at',
    ];

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

                return empty($parts) ? 'کاربر گرامی' : implode(' ', $parts);
            }
        );
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }

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

    public function getLoyaltyPointsBalanceAttribute(): int
    {
        return (int) $this->loyaltyTransactions()->sum('points');
    }

    /**
     * @return array{key: string, label: string, color: string, icon: string, min_points: int, next_points: ?int, discount_percent: int}
     */
    public function getLoyaltyTierAttribute(): array
    {
        $points = $this->loyalty_points_balance;

        if ($points >= 1500) {
            return [
                'key' => 'gold',
                'label' => 'طلایی (VIP)',
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
                'label' => 'نقره‌ای',
                'color' => 'slate',
                'icon' => 'i-lucide-award',
                'min_points' => 500,
                'next_points' => 1500,
                'discount_percent' => 5,
            ];
        }

        return [
            'key' => 'bronze',
            'label' => 'برنزی',
            'color' => 'orange',
            'icon' => 'i-lucide-shield',
            'min_points' => 0,
            'next_points' => 500,
            'discount_percent' => 0,
        ];
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
