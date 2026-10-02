<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Contracts\Models\OrderContract;
use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Enums\ShippingMethod as ShippingMethodEnum;
use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Carbon\Carbon;
use Reyhan\Core\Support\Reyhan;
use Reyhan\Core\Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Marcusvbda\FilamentRealtimeDriver\RealtimeEvent;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property string $order_number
 * @property int|null $user_id
 * @property OrderStatus $status
 * @property ShippingMethodEnum|string|null $shipping_method
 * @property int|null $shipping_method_id
 * @property array<string, mixed>|null $shipping_address
 * @property int $items_subtotal
 * @property int $discount_amount
 * @property int $coupon_discount
 * @property string|null $coupon_code
 * @property int $shipping_fee
 * @property int $tax_amount
 * @property int $wallet_paid_amount
 * @property int $final_payable
 * @property string|null $notes
 * @property bool $is_corporate_invoice
 * @property array<string, mixed>|null $corporate_data
 * @property string|null $tracking_code
 * @property string|null $tracking_url
 * @property Carbon|null $delivery_date
 * @property string|null $delivery_time_slot
 * @property Carbon|null $paid_at
 * @property Carbon|null $shipped_at
 * @property Carbon|null $cancelled_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $user
 * @property-read ShippingMethod|null $shippingMethod
 * @property-read CardTransferReceipt|null $cardTransferReceipt
 * @property-read Collection<int, WalletTransaction> $walletTransactions
 * @property-read Collection<int, OrderItem> $items
 * @property-read Collection<int, Payment> $payments
 * @property-read Payment|null $successfulPayment
 */
#[Guarded(['id'])]
class Order extends Model implements OrderContract, ProvidesActivityTitle
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function activityTitle(): ?string
    {
        return $this->order_number;
    }

    protected static function booted(): void
    {
        static::created(function (self $order): void {
            RealtimeEvent::dispatch('orders', 'OrderCreated', [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status->value,
            ]);
        });

        static::updated(function (self $order): void {
            RealtimeEvent::dispatch('orders', 'OrderUpdated', [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status->value,
            ]);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'shipping_method' => ShippingMethodEnum::class,
            'shipping_method_id' => 'integer',
            'shipping_address' => 'array',
            'items_subtotal' => 'integer',
            'discount_amount' => 'integer',
            'coupon_discount' => 'integer',
            'shipping_fee' => 'integer',
            'tax_amount' => 'integer',
            'wallet_paid_amount' => 'integer',
            'final_payable' => 'integer',
            'is_corporate_invoice' => 'boolean',
            'corporate_data' => 'array',
            'delivery_date' => 'date:Y-m-d',
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
        return $this->belongsTo(Reyhan::userModel());
    }

    /**
     * @return BelongsTo<ShippingMethod, $this>
     */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    /**
     * @return HasOne<CardTransferReceipt, $this>
     */
    public function cardTransferReceipt(): HasOne
    {
        return $this->hasOne(CardTransferReceipt::class);
    }

    /**
     * @return HasMany<WalletTransaction, $this>
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(Reyhan::model('order_item'));
    }

    /**
     * @return HasMany<OrderReturn, $this>
     */
    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class)->latest();
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
    #[Scope]
    protected function pendingPayment(Builder $query): void
    {
        $query->where('status', OrderStatus::PendingPayment);
    }

    /**
     * @param  Builder<Order>  $query
     */
    #[Scope]
    protected function processing(Builder $query): void
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
