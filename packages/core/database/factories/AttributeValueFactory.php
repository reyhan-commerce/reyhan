<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\Attribute;
use Reyhan\Core\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttributeValue>
 */
class AttributeValueFactory extends Factory
{
    protected $model = AttributeValue::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $val = fake()->word();

        return [
            'attribute_id' => Attribute::factory(),
            'value' => $val,
            'label' => 'مقدار '.$val,
            'hex_code' => null,
            'order' => fake()->numberBetween(1, 20),
        ];
    }

    public function color(string $hex, string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $name,
            'label' => $name,
            'hex_code' => $hex,
        ]);
    }
}
