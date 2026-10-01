<?php

declare(strict_types=1);

namespace App\Events\Catalog;

use App\Models\ProductVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ProductRestockedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ProductVariant $variant,
        public int $oldStock,
        public int $newStock,
    ) {}
}
