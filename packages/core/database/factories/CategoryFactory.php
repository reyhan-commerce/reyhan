<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'parent_id' => null,
            'name' => 'دسته‌بندی '.$name,
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'icon' => null,
            'image' => null,
            'order' => fake()->numberBetween(1, 50),
            'is_active' => true,
        ];
    }

    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    public function childOf(Category|int $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent instanceof Category ? $parent->id : $parent,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
