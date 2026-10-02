<?php

declare(strict_types=1);

namespace Reyhan\Core\Events\Inventory;

use Reyhan\Core\Models\ProductVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class StockDepleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ProductVariant $variant,
    ) {}
}
