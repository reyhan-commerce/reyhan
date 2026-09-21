<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ShippingMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
            $number = 'ORD-' . date('ymd') . '-' . strtoupper(Str::random(4));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }
}
