<?php

declare(strict_types=1);

namespace Reyhan\Core\Enums\Concerns;

use Filament\Support\Contracts\HasLabel;

trait HasEnumHelpers
{
    /**
     * Human-readable label (alias of getLabel for Filament & API consistency).
     */
    public function label(): string
    {
        return $this instanceof HasLabel ? $this->getLabel() : (string) $this->value;
    }

    /**
     * Get an associative array of [value => label] for Filament forms and filters.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[(string) $case->value] = $case instanceof HasLabel ? $case->getLabel() : (string) $case->value;
        }

        return $options;
    }

    /**
     * Get all scalar values of the enum cases.
     *
     * @return array<int|string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
