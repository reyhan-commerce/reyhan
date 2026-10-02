<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Enums\AttributeType;
use Reyhan\Core\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attribute>
 */
class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => 'ویژگی '.$name,
            'slug' => fake()->unique()->slug(),
            'type' => AttributeType::Select,
            'order' => fake()->numberBetween(1, 20),
        ];
    }

    public function color(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'رنگ',
            'slug' => 'color-'.fake()->unique()->slug(),
            'type' => AttributeType::Color,
        ]);
    }

    public function select(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AttributeType::Select,
        ]);
    }

    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AttributeType::Text,
        ]);
    }

    public function number(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AttributeType::Number,
        ]);
    }
}
