<?php

namespace App\Services;

use App\Core\LicenceTokenManager;
use App\Models\Licence;
use App\Models\User;

class LicenceUpgradeService
{
    public function __construct(
        private LicenceService $licenceService,
        private LicenceTokenManager $tokenManager
    ) {}

    /**
     * Upgrade user to a predefined licence
     */
    public function upgradeToPredefinedLicence(User $user, int $licenceId): array
    {
        $newLicence = Licence::findOrFail($licenceId);

        if ($this->licenceService->isAdminLicence($newLicence)) {
            return [
                'success' => false,
                'message' => 'Vous ne pouvez pas upgrader vers une licence Admin.',
            ];
        }

        if (!$this->licenceService->isAvailable($newLicence)) {
            return [
                'success' => false,
                'message' => 'Cette licence n\'est pas disponible.',
            ];
        }

        // Générer le nouveau token de licence
        $token = $this->tokenManager->generateToken($user, $newLicence);

        $user->update([
            'licence_id' => $newLicence->id,
            'licence_token' => $token,
        ]);

        return [
            'success' => true,
            'message' => 'Votre licence a été upgradée vers "' . $newLicence->wording . '" avec succès !',
        ];
    }

    /**
     * Create and upgrade to custom licence
     */
    public function upgradeToCustomLicence(User $user, array $data): array
    {
        if ($this->hasCustomLicence($user)) {
            return [
                'success' => false,
                'message' => 'Vous avez déjà créé une licence personnalisée. Chaque utilisateur ne peut créer qu\'une seule licence personnalisée.',
            ];
        }

        $newLicence = $this->licenceService->create([
            'wording' => $data['wording'],
            'description' => 'Licence personnalisée pour ' . $user->name,
            'max_apps' => $data['max_apps'],
            'max_executions_per_24h' => $data['max_executions_per_24h'],
            'valid_from' => now(),
            'valid_to' => now()->addYear(),
            'status' => 'ACTIVE',
            'is_active' => true,
            'is_custom' => true,
            'created_by_user_id' => $user->id,
        ]);

        // Générer le nouveau token de licence
        $token = $this->tokenManager->generateToken($user, $newLicence);

        $user->update([
            'licence_id' => $newLicence->id,
            'licence_token' => $token,
        ]);

        return [
            'success' => true,
            'message' => 'Votre licence personnalisée "' . $newLicence->wording . '" a été créée et activée avec succès !',
        ];
    }

    /**
     * Check if user has already created a custom licence
     */
    public function hasCustomLicence(User $user): bool
    {
        return Licence::where('created_by_user_id', $user->id)
            ->where('is_custom', true)
            ->exists();
    }
}
