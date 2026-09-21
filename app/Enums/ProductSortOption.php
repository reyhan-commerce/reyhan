<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductSortOption: string implements HasLabel
{
    case Latest = 'latest';
    case Cheapest = 'cheapest';
    case Expensive = 'expensive';
    case Featured = 'featured';

    public function getLabel(): string
    {
        return match ($this) {
            self::Latest => 'جدیدترین',
            self::Cheapest => 'ارزان‌ترین',
            self::Expensive => 'گران‌ترین',
            self::Featured => 'محصولات برگزیده',
        };
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
