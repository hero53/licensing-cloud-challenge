<?php

namespace App\Console\Commands;

use App\Core\LicenceTokenManager;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateLicenceTokens extends Command
{
    protected $signature = 'licence:generate-tokens';
    protected $description = 'Générer les tokens de licence pour tous les utilisateurs';

    public function __construct(
        private LicenceTokenManager $tokenManager
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Génération des tokens de licence pour tous les utilisateurs...');

        $users = User::with('licence')->get();
        $count = 0;
        $errors = 0;

        foreach ($users as $user) {
            try {
                if (!$user->licence) {
                    $this->warn("Utilisateur {$user->name} (ID: {$user->id}) n'a pas de licence");
                    continue;
                }

                $token = $this->tokenManager->generateToken($user, $user->licence);
                $user->licence_token = $token;
                $user->save();

                $count++;
                $this->line("✓ Token généré pour {$user->name}");
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Erreur pour {$user->name}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Terminé ! {$count} tokens générés, {$errors} erreurs");

        return Command::SUCCESS;
    }
}
