<?php

namespace Database\Seeders;

use App\Models\TypeUser;
use Illuminate\Database\Seeder;

class TypeUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer le type Administrateur
        TypeUser::firstOrCreate(
            ['slug' => 'admin'],
            [
                'wording' => 'Administrateur',
                'is_active' => true,
            ]
        );

        // Créer le type Client
        TypeUser::firstOrCreate(
            ['slug' => 'client'],
            [
                'wording' => 'Client',
                'is_active' => true,
            ]
        );
    }
}
