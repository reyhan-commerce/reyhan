<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Enums\PaymentStatus;
use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Carbon\Carbon;
use Reyhan\Core\Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $user_id
 * @property PaymentGateway $gateway
 * @property PaymentStatus $status
 * @property int $amount
 * @property string|null $reference_id
 * @property string|null $tracking_code
 * @property string|null $card_pan
 * @property array<string, mixed>|null $gateway_response
 * @property Carbon|null $paid_at
 * @property-read Order|null $order
 * @property-read User|null $user
 */
#[Guarded(['id'])]
class Payment extends Model implements ProvidesActivityTitle
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logExcept(['created_at', 'updated_at', 'gateway_response'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function activityTitle(): ?string
    {
        return ($this->tracking_code ?: $this->reference_id) ?? ('#'.$this->id);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gateway' => PaymentGateway::class,
            'status' => PaymentStatus::class,
            'amount' => 'integer',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<Payment>  $query
     */
    #[Scope]
    protected function success(Builder $query): void
    {
        $query->where('status', PaymentStatus::Success);
    }

    public static function generateTrackingCode(): string
    {
        return (string) random_int(10000000, 99999999);
    }
}
