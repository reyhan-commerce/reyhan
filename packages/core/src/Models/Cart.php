<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Contracts\Models\CartContract;
use Reyhan\Core\Database\Factories\CartFactory;
use Reyhan\Core\Support\Reyhan;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property int|null $coupon_id
 * @property-read User|null $user
 * @property-read Coupon|null $coupon
 * @property-read Collection<int, CartItem> $items
 */
#[Guarded(['id'])]
class Cart extends Model implements CartContract
{
    /** @use HasFactory<CartFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Reyhan::userModel());
    }

    /**
     * @return BelongsTo<Coupon, $this>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(Reyhan::model('cart_item'));
    }

    public function totalItemsQuantity(): int
    {
        return (int) $this->items()->sum('quantity');
    }
}
