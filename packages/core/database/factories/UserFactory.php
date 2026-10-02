<?php

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'mobile' => '09'.fake()->unique()->numerify('#########'),
            'national_code' => fake()->unique()->numerify('##########'),
            'email' => fake()->unique()->userName().'.'.uniqid().'@example.com',
            'avatar' => null,
            'is_active' => true,
            'mobile_verified_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'mobile_verified_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withLoyaltyPoints(int $points = 100): static
    {
        return $this->afterCreating(function (User $user) use ($points) {
            $user->awardLoyaltyPoints($points, 'initial_bonus', 'امتیاز اولیه کاربر');
        });
    }
}
