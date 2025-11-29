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
        // Validité de 30 jours à partir de la date de début
        $validTo = (clone $validFrom)->modify('+30 days');

        return [
            'wording' => fake()->words(3, true) . ' Licence',
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'max_apps' => fake()->numberBetween(1, 20),
            'max_executions_per_24h' => fake()->numberBetween(100, 10000),
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'status' => fake()->randomElement(['ACTIVE', 'SUSPENDED', 'EXPIRED']),
            'is_active' => fake()->boolean(80),
        ];
    }

    /**
     * Indicate that the licence is a free plan.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'wording' => 'Free',
            'slug' => 'free',
            'description' => 'Plan gratuit avec fonctionnalités limitées',
            'max_apps' => 3,
            'max_executions_per_24h' => 10,
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the licence is an admin plan.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'wording' => 'Admin',
            'slug' => 'admin',
            'description' => 'Plan administrateur avec fonctionnalités complètes',
            'max_apps' => 10,
            'max_executions_per_24h' => 300,
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);
    }
}
