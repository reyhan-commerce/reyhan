<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use App\Models\City;
use App\Models\Province;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'province_id' => Province::factory(),
            'city_id' => fn (array $attributes) => City::factory()->create(['province_id' => $attributes['province_id']])->id,
            'recipient_name' => fake()->name(),
            'recipient_mobile' => '09'.fake()->numerify('#########'),
            'postal_code' => fake()->numerify('##########'),
            'address_line' => 'خیابان '.fake()->streetName().'، کوچه '.fake()->word().'، پلاک '.fake()->buildingNumber(),
            'building_number' => (string) fake()->buildingNumber(),
            'unit' => (string) fake()->numberBetween(1, 20),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
