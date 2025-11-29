<?php

namespace Database\Seeders;

use App\Core\LicenceTokenManager;
use App\Models\Licence;
use App\Models\TypeUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tokenManager = app(LicenceTokenManager::class);

        // Récupérer les types d'utilisateurs
        $adminType = TypeUser::where('slug', 'admin')->first();
        $clientType = TypeUser::where('slug', 'client')->first();

        // Récupérer les licences
        $freeLicence = Licence::where('slug', 'free')->first();
        $adminLicence = Licence::where('slug', 'admin')->first();

        // Créer 1 utilisateur admin avec le mot de passe "admin" et la licence Admin
        $adminUser = User::factory()->create([
            'type_user_id' => $adminType->id,
            'licence_id' => $adminLicence->id,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin'),
            'is_active' => true,
        ]);

        // Générer le token pour l'admin
        $adminUser->licence_token = $tokenManager->generateToken($adminUser, $adminLicence);
        $adminUser->save();

        // Créer 1 utilisateur client avec le mot de passe "client" et la licence Free
        $clientUser = User::factory()->create([
            'type_user_id' => $clientType->id,
            'licence_id' => $freeLicence->id,
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => bcrypt('client'),
            'is_active' => true,
        ]);

        // Générer le token pour le client
        $clientUser->licence_token = $tokenManager->generateToken($clientUser, $freeLicence);
        $clientUser->save();
    }
}
