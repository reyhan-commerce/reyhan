<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AttributeType: string implements HasColor, HasIcon, HasLabel
{
    case Text = 'text';
    case Color = 'color';
    case Number = 'number';
    case Select = 'select';

    public function getLabel(): string
    {
        return match ($this) {
            self::Text => 'متن ساده (Text)',
            self::Color => 'رنگ انتخابی با کد هگز (Color Swatch)',
            self::Number => 'عدد و مقیاس (Number)',
            self::Select => 'منوی کشویی / لیست گزینه‌ای (Select)',
        };
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
