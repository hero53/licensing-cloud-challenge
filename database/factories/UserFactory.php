<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\User::class;

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
        $clientType = \App\Models\TypeUser::firstOrCreate(
            ['slug' => 'client'],
            ['wording' => 'Client', 'is_active' => true]
        );

        // Récupérer une licence par défaut (Free)
        $defaultLicence = \App\Models\Licence::where('slug', 'free')->first();

        return [
            'type_user_id' => $clientType->id,
            'licence_id' => $defaultLicence?->id,
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => $this->faker->boolean(85),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Configure the model factory (appelé après la création)
     */
    public function configure()
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            // Générer le token de licence après la création
            if ($user->licence_id && $user->licence) {
                $tokenManager = app(\App\Core\LicenceTokenManager::class);
                $user->licence_token = $tokenManager->generateToken($user, $user->licence);
                $user->save();
            }
        });
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(function (array $attributes) {
            $adminType = \App\Models\TypeUser::firstOrCreate(
                ['slug' => 'admin'],
                ['wording' => 'Admin', 'is_active' => true]
            );

            return [
                'type_user_id' => $adminType->id,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'is_active' => true,
            ];
        });
    }

    /**
     * Indicate that the user is a client.
     */
    public function client(): static
    {
        return $this->state(function (array $attributes) {
            $clientType = \App\Models\TypeUser::firstOrCreate(
                ['slug' => 'client'],
                ['wording' => 'Client', 'is_active' => true]
            );

            return [
                'type_user_id' => $clientType->id,
            ];
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model does not have two-factor authentication configured.
     */
    public function withoutTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }
}
