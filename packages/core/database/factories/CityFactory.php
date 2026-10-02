<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\City;
use Reyhan\Core\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'province_id' => Province::factory(),
            'name' => fake()->city(),
            'slug' => fake()->unique()->slug(),
            'latitude' => fake()->latitude(25.0, 39.0),
            'longitude' => fake()->longitude(44.0, 63.0),
            'postal_prefix' => fake()->numerify('###'),
            'is_active' => true,
            'order' => fake()->numberBetween(1, 100),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
