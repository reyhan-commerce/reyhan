<?php

declare(strict_types=1);

namespace Reyhan\Core\Enums;

use Reyhan\Core\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BannerPosition: string implements HasColor, HasLabel
{
    use HasEnumHelpers;

    case HomeSlider = 'home_slider';
    case HomeMiddle = 'home_middle';
    case HomeGrid = 'home_grid';
    case Sidebar = 'sidebar';

    public function getLabel(): string
    {
        return __('enums.banner_position.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HomeSlider => 'primary',
            self::HomeMiddle => 'info',
            self::HomeGrid => 'warning',
            self::Sidebar => 'gray',
        };
    }

    public function color(): string
    {
        return $this->getColor();
    }
}
