<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\JobApplication;
use App\Models\JobExecution;
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
        // Créer les types d'utilisateurs
        $this->call(TypeUserSeeder::class);

        // Créer les licences (Free et Admin)
        $this->call(LicenceSeeder::class);

        // Créer les utilisateurs principaux (admin et client)
        $this->call(UserSeeder::class);

        // Récupérer les types d'utilisateurs
        $clientType = TypeUser::where('slug', 'client')->first();

        // Récupérer les licences
        $freeLicence = Licence::where('slug', 'free')->first();
        $adminLicence = Licence::where('slug', 'admin')->first();

        // Créer des licences supplémentaires
        $additionalLicences = Licence::factory(13)->create();
        $allLicences = collect([$freeLicence, $adminLicence])->merge($additionalLicences);

        // Récupérer les utilisateurs principaux
        $adminUser = User::where('email', 'admin@example.com')->first();
        $clientUser = User::where('email', 'client@example.com')->first();

        // Créer 18 utilisateurs clients supplémentaires
        $users = User::factory(18)->create([
            'type_user_id' => $clientType->id,
            'licence_id' => fn() => $allLicences->random()->id,
        ]);

        // Créer une application pour chaque utilisateur avec un job_application associé
        $this->call(ApplicationSeeder::class);

        // Récupérer tous les utilisateurs et toutes les applications et job_applications
        $allUsers = User::all();
        $allApplications = Application::all();
        $allJobApplications = JobApplication::all();

        // Créer des job executions
        JobExecution::factory(200)->create(fn() => [
            'job_application_id' => $allJobApplications->random()->id,
            'user_id' => $allUsers->random()->id,
        ]);
    }
}
