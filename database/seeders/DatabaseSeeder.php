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
        // Créer 1 utilisateur admin
        $adminUser = User::factory()->admin()->create();

        // Créer 19 utilisateurs clients
        $users = User::factory(19)->create();

        // Collecter tous les utilisateurs
        $allUsers = collect([$adminUser])->merge($users);

        $licences = Licence::factory(15)->create(function () use ($allUsers) {
            return [
                'user_id' => $allUsers->random()->id,
            ];
        });

        $applications = Application::factory(30)->create(function () use ($licences) {
            return [
                'licence_id' => $licences->random()->id,
            ];
        });

        $jobApplications = JobApplication::factory(50)->create(function () use ($applications) {
            return [
                'application_id' => $applications->random()->id,
            ];
        });

        JobExecution::factory(200)->create(function () use ($jobApplications, $allUsers) {
            return [
                'job_application_id' => $jobApplications->random()->id,
                'user_id' => $allUsers->random()->id,
            ];
        });
    }
}
