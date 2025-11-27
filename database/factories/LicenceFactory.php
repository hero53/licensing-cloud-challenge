<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Licence>
 */
class LicenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $validFrom = fake()->dateTimeBetween('-1 year', 'now');
        $validTo = fake()->dateTimeBetween($validFrom, '+2 years');

        return [
            'user_id' => \App\Models\User::factory(),
            'wording' => fake()->words(3, true) . ' Licence',
            'description' => fake()->sentence(),
            'max_apps' => fake()->numberBetween(1, 20),
            'max_executions_per_24h' => fake()->numberBetween(100, 10000),
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'status' => fake()->randomElement(['ACTIVE', 'SUSPENDED', 'EXPIRED']),
            'is_active' => fake()->boolean(80),
        ];
    }
}
