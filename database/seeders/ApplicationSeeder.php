<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les utilisateurs
        $users = User::all();

        // Pour chaque utilisateur, créer une application
        foreach ($users as $user) {
            // Créer une application pour cet utilisateur
            $application = Application::create([
                'user_id' => $user->id,
                'wording' => 'Application ' . $user->name,
                'description' => 'Application de test pour ' . $user->name,
                'is_active' => true,
            ]);

            // Créer un job_application associé à cette application
            JobApplication::create([
                'application_id' => $application->id,
                'wording' => 'Job pour ' . $application->wording,
            ]);
        }
    }
}
