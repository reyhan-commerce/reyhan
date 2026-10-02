<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $transaction_number
 * @property int|null $order_id
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property string $description
 * @property Carbon $transacted_at
 * @property-read Order|null $order
 * @property-read Collection<int, LedgerEntry> $entries
 */
#[Guarded(['id'])]
class LedgerTransaction extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transacted_at' => 'datetime',
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
     * @return HasMany<LedgerEntry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Generate unique journal transaction number (e.g., JRN-261001-A9F4).
     */
    public static function generateTransactionNumber(): string
    {
        do {
            $number = 'JRN-'.date('ymd').'-'.strtoupper(Str::random(4));
        } while (static::where('transaction_number', $number)->exists());

        return $number;
    }
}
