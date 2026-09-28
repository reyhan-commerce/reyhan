<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

use Filament\Support\Contracts\HasLabel;

trait HasEnumHelpers
{
    /**
     * Human-readable label (alias of getLabel for Filament & API consistency).
     */
    public function label(): string
    {
        if ($this instanceof HasLabel) {
            return $this->getLabel();
        }

        return (string) $this->value;
    }

    /**
     * Get an associative array of [value => label] for Filament forms and filters.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(function (self $case): array {
                $label = match (true) {
                    $case instanceof HasLabel => $case->getLabel(),
                    method_exists($case, 'label') => $case->label(),
                    default => $case->value,
                };

                return [(string) $case->value => (string) $label];
            })
            ->all();
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
