<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SpecificationGroup;
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
