<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Contracts\Models\UserContract;
use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Carbon\Carbon;
use Reyhan\Core\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
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
 * @property int $wallet_balance
 * @property string|null $referral_code
 * @property int|null $referred_by
 * @property-read int $loyalty_points_balance
 * @property-read array{key: string, label: string, color: string, icon: string, min_points: int, next_points: ?int, discount_percent: int} $loyalty_tier
 * @property-read string $full_name
 * @property-read Collection<int, WalletTransaction> $walletTransactions
 * @property-read User|null $referrer
 * @property-read Collection<int, User> $referredUsers
 * @property-read Collection<int, Referral> $referralsSent
 * @property-read Referral|null $referralReceived
 */
#[Guarded(['id'])]
#[Hidden(['remember_token'])]
class User extends Authenticatable implements ProvidesActivityTitle, UserContract
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, LogsActivity, Notifiable, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (empty($user->referral_code)) {
                $user->referral_code = strtoupper(Str::random(8));
            }
        });
    }

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
            'wallet_balance' => 'integer',
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
     * @return HasMany<WalletTransaction, $this>
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    /**
     * @return HasMany<CardTransferReceipt, $this>
     */
    public function cardTransferReceipts(): HasMany
    {
        return $this->hasMany(CardTransferReceipt::class)->latest();
    }

    /**
     * @return HasMany<OrderReturn, $this>
     */
    public function orderReturns(): HasMany
    {
        return $this->hasMany(OrderReturn::class)->latest();
    }

    /**
     * @return HasMany<SupportTicket, $this>
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class)->latest();
    }

    /**
     * @return HasMany<ProductQuestion, $this>
     */
    public function productQuestions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class)->latest();
    }

    /**
     * @return HasMany<ProductAnswer, $this>
     */
    public function productAnswers(): HasMany
    {
        return $this->hasMany(ProductAnswer::class)->latest();
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
     * @return BelongsTo<User, $this>
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function referredUsers(): HasMany
    {
        return $this->hasMany(self::class, 'referred_by');
    }

    /**
     * @return HasMany<Referral, $this>
     */
    public function referralsSent(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * @return HasOne<Referral, $this>
     */
    public function referralReceived(): HasOne
    {
        return $this->hasOne(Referral::class, 'referred_id');
    }

    /**
     * @return HasMany<AbandonedCartLog, $this>
     */
    public function abandonedCartLogs(): HasMany
    {
        return $this->hasMany(AbandonedCartLog::class);
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
