<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\SpecificationGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpecificationGroup>
 */
class SpecificationGroupFactory extends Factory
{
    protected $model = SpecificationGroup::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'order' => 1,
        ];
    }
}
