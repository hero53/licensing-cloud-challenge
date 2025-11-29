<?php

namespace App\Core;

use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Models\Licence;

/**
 * License token manager
 *
 * Creates an encoded token containing all license information
 * to avoid SQL queries on every verification
 */
class LicenceTokenManager
{
    /**
     * Generate a token for a user and their license
     * The token contains all necessary information
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

        // Convert to JSON then encrypt
        $json = json_encode($data);
        return Crypt::encryptString($json);
    }

    /**
     * Decode a token and return license information
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
     * Check if the token is still valid (not expired)
     */
    public function isTokenValid(string $token): bool
    {
        try {
            $data = $this->decodeToken($token);

            // Check that the license is not expired
            if (strtotime($data['valid_to']) < time()) {
                return false;
            }

            // Check that the status is ACTIVE
            if ($data['status'] !== 'ACTIVE') {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get license information from the token
     * Returns a stdClass object for easier access (->max_apps instead of ['max_apps'])
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
     * Get a specific value from the token
     */
    public function getTokenValue(string $token, string $key): mixed
    {
        $data = $this->decodeToken($token);
        return $data[$key] ?? null;
    }

    /**
     * Regenerate a user's token
     * Used when upgrading a license
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
