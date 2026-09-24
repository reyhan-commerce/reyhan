<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ShippingMethod;
use Carbon\Carbon;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $order_number
 * @property int|null $user_id
 * @property OrderStatus $status
 * @property ShippingMethod|null $shipping_method
 * @property array<string, mixed>|null $shipping_address
 * @property int $items_subtotal
 * @property int $discount_amount
 * @property int $coupon_discount
 * @property string|null $coupon_code
 * @property int $shipping_fee
 * @property int $final_payable
 * @property string|null $notes
 * @property Carbon|null $paid_at
 * @property Carbon|null $shipped_at
 * @property Carbon|null $cancelled_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $user
 * @property-read Collection<int, OrderItem> $items
 * @property-read Collection<int, Payment> $payments
 * @property-read Payment|null $successfulPayment
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'shipping_method' => ShippingMethod::class,
            'shipping_address' => 'array',
            'items_subtotal' => 'integer',
            'discount_amount' => 'integer',
            'coupon_discount' => 'integer',
            'shipping_fee' => 'integer',
            'final_payable' => 'integer',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'cancelled_at' => 'datetime',
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
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasOne<Payment, $this>
     */
    public function successfulPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->where('status', 'success');
    }

    /**
     * @param  Builder<Order>  $query
     */
    public function scopePendingPayment(Builder $query): void
    {
        $query->where('status', OrderStatus::PendingPayment);
    }

    /**
     * @param  Builder<Order>  $query
     */
    public function scopeProcessing(Builder $query): void
    {
        $query->where('status', OrderStatus::Processing);
    }

    /**
     * Generate unique human-readable order number (e.g., ORD-260921-A8F2).
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.date('ymd').'-'.strtoupper(Str::random(4));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }
}
