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
        $typeUsers = TypeUser::factory(5)->create();

        $users = User::factory(20)->create(function () use ($typeUsers) {
            return [
                'type_user_id' => $typeUsers->random()->id,
            ];
        });

        $licences = Licence::factory(15)->create(function () use ($users) {
            return [
                'user_id' => $users->random()->id,
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

        JobExecution::factory(200)->create(function () use ($jobApplications, $users) {
            return [
                'job_application_id' => $jobApplications->random()->id,
                'user_id' => $users->random()->id,
            ];
        });
    }
}
