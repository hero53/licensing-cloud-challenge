<?php

namespace App\Services;

use App\Core\LicenceTokenManager;
use App\Core\SlidingWindow;
use App\Models\Application;
use App\Models\Licence;
use App\Models\User;

class ExecutionLimitService
{
    public function __construct(
        private SlidingWindow $slidingWindow,
        private LicenceTokenManager $tokenManager
    ) {}
    /**
     * Check if user has reached execution limit
     * Utilise le token de licence (pas de requête SQL) et la fenêtre glissante
     */
    public function hasReachedExecutionLimit(User $user): bool
    {
        // Décoder le token pour avoir les infos de la licence (pas de requête SQL!)
        $licenceData = $this->tokenManager->getLicenceData($user->licence_token);

        // Pour chaque application de l'utilisateur, nettoyer la fenêtre glissante
        $user->applications()->each(function ($application) use ($user) {
            $this->slidingWindow->nettoyerAnciennesExecutions($user->id, $application->id);
        });

        $executionsLast24h = $this->getExecutionsLast24h($user);
        return $executionsLast24h >= $licenceData->max_executions_per_24h;
    }

    /**
     * Check if user has reached application limit
     * Utilise le token de licence (pas de requête SQL)
     */
    public function hasReachedApplicationLimit(User $user): bool
    {
        // Décoder le token pour avoir les infos de la licence
        $licenceData = $this->tokenManager->getLicenceData($user->licence_token);

        $currentApplicationsCount = $user->applications()
            ->where('is_active', true)
            ->count();

        return $currentApplicationsCount >= $licenceData->max_apps;
    }

    /**
     * Get executions count in the last 24 hours
     * Compte seulement les exécutions actives (is_active = true)
     */
    public function getExecutionsLast24h(User $user): int
    {
        return $user->userApplicationJobs()
            ->where('is_active', true)
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }

    /**
     * Get executions count for today
     */
    public function getExecutionsToday(User $user): int
    {
        return $user->userApplicationJobs()
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Get executions count for a specific application in the last 24h
     * Nettoie d'abord la fenêtre glissante puis compte les exécutions actives
     */
    public function getApplicationExecutionsLast24h(User $user, Application $application): int
    {
        // Nettoyer la fenêtre glissante avant de compter
        $this->slidingWindow->nettoyerAnciennesExecutions($user->id, $application->id);

        return $user->userApplicationJobs()
            ->where('application_id', $application->id)
            ->where('is_active', true)
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }

    /**
     * Record a job execution
     * Nettoie la fenêtre glissante AVANT de créer la nouvelle exécution
     */
    public function recordExecution(User $user, Application $application, int $jobApplicationId): void
    {
        // 1. Nettoyer la fenêtre glissante avant de créer la nouvelle exécution
        $this->slidingWindow->nettoyerAnciennesExecutions($user->id, $application->id);

        // 2. Créer la nouvelle exécution (is_active sera automatiquement true par défaut)
        $user->userApplicationJobs()->create([
            'application_id' => $application->id,
            'job_application_id' => $jobApplicationId,
        ]);
    }

    /**
     * Get execution statistics for user
     * Utilise le token de licence
     */
    public function getExecutionStats(User $user): array
    {
        // Décoder le token pour avoir les infos de la licence
        $licenceData = $this->tokenManager->getLicenceData($user->licence_token);

        return [
            'executionsToday' => $this->getExecutionsToday($user),
            'maxExecutionsPerDay' => $licenceData->max_executions_per_24h,
            'executionsLast24h' => $this->getExecutionsLast24h($user),
            'remainingExecutions' => max(0, $licenceData->max_executions_per_24h - $this->getExecutionsLast24h($user)),
        ];
    }

    /**
     * Nettoie toutes les fenêtres glissantes pour toutes les applications d'un utilisateur
     * Utile pour faire un grand nettoyage avant d'afficher les statistiques
     */
    public function nettoyerToutesLesFenetres(User $user): int
    {
        $totalDesactive = 0;

        // Pour chaque application de l'utilisateur
        $user->applications()->each(function ($application) use ($user, &$totalDesactive) {
            $nbDesactive = $this->slidingWindow->nettoyerAnciennesExecutions($user->id, $application->id);
            $totalDesactive += $nbDesactive;
        });

        return $totalDesactive;
    }
}
