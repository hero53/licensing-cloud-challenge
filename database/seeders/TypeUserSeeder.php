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
        TypeUser::factory()->admin()->create();

        // Créer le type Client
        TypeUser::factory()->client()->create();
    }
}
