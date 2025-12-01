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
        TypeUser::create([
            'wording' => 'Administrateur',
            'slug' => 'admin',
            'is_active' => true,
        ]);

        // Créer le type Client
        TypeUser::create([
            'wording' => 'Client',
            'slug' => 'client',
            'is_active' => true,
        ]);
    }
}
