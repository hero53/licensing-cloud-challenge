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
        Licence::factory()->free()->create([
            'valid_from' => $startDate,
            'valid_to' => $startDate->copy()->addDays(30),
        ]);

        // Créer la licence Admin (validité 30 jours)
        Licence::factory()->admin()->create([
            'valid_from' => $startDate,
            'valid_to' => $startDate->copy()->addDays(30),
        ]);
    }
}
