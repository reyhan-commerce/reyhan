<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\Specification;
use Reyhan\Core\Models\SpecificationGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Specification>
 */
class SpecificationFactory extends Factory
{
    protected $model = Specification::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'specification_group_id' => SpecificationGroup::factory(),
            'name' => fake()->unique()->word(),
            'unit' => null,
            'type' => 'text',
            'options' => null,
            'is_filterable' => true,
            'order' => 1,
        ];
    }
}
