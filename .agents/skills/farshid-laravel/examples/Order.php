<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatusEnum::class,
            'total_amount' => 'integer',
            'discount_amount' => 'integer',
            'is_paid' => 'boolean',
            'metadata' => 'array',
            'paid_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * User who owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Line items associated with this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // -------------------------------------------------------------------------
    // Domain State Methods (Model-Owned Logic)
    // -------------------------------------------------------------------------

    public function isPaid(): bool
    {
        return $this->status === OrderStatusEnum::PAID;
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatusEnum::PENDING;
    }

    public function canBeCancelled(): bool
    {
        return $this->isPending() && ! $this->isPaid();
    }

    public function markAsPaid(string $paymentReference): void
    {
        $this->update([
            'status' => OrderStatusEnum::PAID,
            'is_paid' => true,
            'payment_reference' => $paymentReference,
            'paid_at' => CarbonImmutable::now(),
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update([
            'status' => OrderStatusEnum::CANCELLED,
        ]);
    }
}
