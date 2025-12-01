<?php

namespace Database\Seeders;

use App\Models\Licence;
use Illuminate\Database\Seeder;

class LicenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startDate = now();

        // Créer la licence Free (validité 30 jours)
        Licence::create([
            'wording' => 'Free',
            'slug' => 'free',
            'description' => 'Plan gratuit avec fonctionnalités limitées',
            'max_apps' => 3,
            'max_executions_per_24h' => 10,
            'valid_from' => $startDate,
            'valid_to' => $startDate->copy()->addDays(30),
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        // Créer la licence Admin (validité 30 jours)
        Licence::create([
            'wording' => 'Admin',
            'slug' => 'admin',
            'description' => 'Plan administrateur avec fonctionnalités complètes',
            'max_apps' => 10,
            'max_executions_per_24h' => 300,
            'valid_from' => $startDate,
            'valid_to' => $startDate->copy()->addDays(30),
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);
    }
}
