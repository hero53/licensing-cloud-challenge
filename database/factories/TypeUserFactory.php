<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TypeUser>
 */
class TypeUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wording' => fake()->unique()->randomElement(['Admin', 'Manager', 'Developer', 'Client', 'Guest', 'Moderator', 'Viewer', 'Editor']),
            'is_active' => fake()->boolean(90),
        ];
    }
}
