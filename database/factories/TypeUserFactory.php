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
            'wording' => 'Client',
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the type user is admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'wording' => 'Admin',
        ]);
    }

    /**
     * Indicate that the type user is client.
     */
    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'wording' => 'Client',
        ]);
    }
}
