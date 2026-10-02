<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Reyhan\Core\Enums\OrderReturnStatus;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $return_number
 * @property int $order_id
 * @property int $user_id
 * @property OrderReturnStatus $status
 * @property string $reason
 * @property array<string>|null $photos
 * @property int $refund_amount
 * @property string|null $admin_notes
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order|null $order
 * @property-read User|null $user
 * @property-read User|null $reviewer
 * @property-read Collection<int, OrderReturnItem> $items
 */
#[Guarded(['id'])]
final class OrderReturn extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderReturnStatus::class,
            'photos' => 'array',
            'refund_amount' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderReturnItem::class);
    }
}
