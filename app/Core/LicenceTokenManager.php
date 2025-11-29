<?php

namespace App\Core;

use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Models\Licence;

/**
 * Gestionnaire de tokens de licence
 *
 * Permet de créer un token encodé contenant toutes les infos de la licence
 * pour éviter les requêtes SQL à chaque vérification
 */
class LicenceTokenManager
{
    /**
     * Générer un token pour un utilisateur et sa licence
     * Le token contient toutes les infos nécessaires
     */
    public function generateToken(User $user, Licence $licence): string
    {
        $data = [
            'user_id' => $user->id,
            'licence_id' => $licence->id,
            'wording' => $licence->wording,
            'description' => $licence->description,
            'max_apps' => $licence->max_apps,
            'max_executions_per_24h' => $licence->max_executions_per_24h,
            'valid_from' => $licence->valid_from->toDateTimeString(),
            'valid_to' => $licence->valid_to->toDateTimeString(),
            'status' => $licence->status,
            'is_custom' => $licence->is_custom,
            'generated_at' => now()->toDateTimeString(),
        ];

        // Convertir en JSON puis encrypter
        $json = json_encode($data);
        return Crypt::encryptString($json);
    }

    /**
     * Décoder un token et retourner les infos de la licence
     */
    public function decodeToken(string $token): array
    {
        try {
            $decrypted = Crypt::decryptString($token);
            return json_decode($decrypted, true);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Token invalide ou corrompu');
        }
    }

    /**
     * Vérifier si le token est encore valide (pas expiré)
     */
    public function isTokenValid(string $token): bool
    {
        try {
            $data = $this->decodeToken($token);

            // Vérifier que la licence n'est pas expirée
            if (strtotime($data['valid_to']) < time()) {
                return false;
            }

            // Vérifier que le statut est ACTIVE
            if ($data['status'] !== 'ACTIVE') {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtenir les infos de la licence depuis le token
     * Retourne un objet stdClass pour faciliter l'accès (->max_apps au lieu de ['max_apps'])
     */
    public function getLicenceData(?string $token): object
    {
        if (empty($token)) {
            throw new \InvalidArgumentException('Le token de licence est vide. Veuillez régénérer le token.');
        }

        $data = $this->decodeToken($token);
        return (object) $data;
    }

    /**
     * Obtenir une info spécifique du token
     */
    public function getTokenValue(string $token, string $key): mixed
    {
        $data = $this->decodeToken($token);
        return $data[$key] ?? null;
    }

    /**
     * Régénérer le token d'un utilisateur
     * Utilisé lors d'un upgrade de licence
     */
    public function regenerateUserToken(User $user): string
    {
        $licence = $user->licence;
        $newToken = $this->generateToken($user, $licence);

        $user->licence_token = $newToken;
        $user->save();

        return $newToken;
    }
}
