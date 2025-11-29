<?php

namespace Database\Seeders;

use App\Models\Licence;
use App\Models\TypeUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer les 2 types d'utilisateurs (Administrateur et Client)
        $this->call(TypeUserSeeder::class);

        // Créer les 2 licences (Free et Admin)
        $this->call(LicenceSeeder::class);

        // Créer les 2 utilisateurs principaux (admin et client)
        $this->call(UserSeeder::class);

        // Créer une application pour chaque utilisateur avec un job_application associé
        $this->call(ApplicationSeeder::class);
    }
}
