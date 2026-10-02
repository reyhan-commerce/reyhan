<?php

declare(strict_types=1);

namespace Reyhan\Core\Data\Wishlist;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class WishlistToggleResultData extends Data
{
    public function __construct(
        #[MapName('in_wishlist')]
        public bool $inWishlist,
        public string $message,
    ) {}
}
