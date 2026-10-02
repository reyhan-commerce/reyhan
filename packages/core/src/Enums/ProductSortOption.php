<?php

declare(strict_types=1);

namespace Reyhan\Core\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductSortOption: string implements HasLabel
{
    case Latest = 'latest';
    case Cheapest = 'cheapest';
    case Expensive = 'expensive';
    case Featured = 'featured';
    case Bestselling = 'bestselling';
    case Popular = 'popular';

    public function getLabel(): string
    {
        return match ($this) {
            self::Latest => __('Latest'),
            self::Cheapest => __('Cheapest'),
            self::Expensive => __('Most Expensive'),
            self::Featured => __('Featured Products'),
            self::Bestselling, self::Popular => __('Best Selling'),
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
