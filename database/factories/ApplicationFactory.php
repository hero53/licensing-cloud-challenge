<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'licence_id' => \App\Models\Licence::factory(),
            'wording' => fake()->words(2, true) . ' App',
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(85),
        ];
    }
}
