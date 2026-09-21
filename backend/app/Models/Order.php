<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ShippingMethod;
use Carbon\CarbonImmutable;
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
 * @property CarbonImmutable|null $paid_at
 * @property CarbonImmutable|null $shipped_at
 * @property CarbonImmutable|null $cancelled_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read User|null $user
 * @property-read Collection<int, OrderItem> $items
 */
class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'shipping_method',
        'shipping_address',
        'items_subtotal',
        'discount_amount',
        'coupon_discount',
        'coupon_code',
        'shipping_fee',
        'final_payable',
        'notes',
        'paid_at',
        'shipped_at',
        'cancelled_at',
    ];

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function successfulPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->where('status', 'success');
    }

    public function scopePendingPayment(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PendingPayment);
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Processing);
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
