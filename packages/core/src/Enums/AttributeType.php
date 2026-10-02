<?php

declare(strict_types=1);

namespace Reyhan\Core\Enums;

use Reyhan\Core\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AttributeType: string implements HasColor, HasIcon, HasLabel
{
    use HasEnumHelpers;

    case Text = 'text';
    case Color = 'color';
    case Number = 'number';
    case Select = 'select';

    public function getLabel(): string
    {
        return __('enums.attribute_type.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Text => 'gray',
            self::Color => 'warning',
            self::Number => 'info',
            self::Select => 'primary',
        };
    }

    public function color(): string
    {
        return $this->getColor();
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Text => 'heroicon-o-document-text',
            self::Color => 'heroicon-o-swatch',
            self::Number => 'heroicon-o-variable',
            self::Select => 'heroicon-o-list-bullet',
        };
    }
}
