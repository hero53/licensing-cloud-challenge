<?php

namespace App\Core;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Classe pour gérer la fenêtre glissante de 24 heures
 *
 * Explication simple:
 * Une fenêtre glissante de 24h = on compte seulement les exécutions des 24 dernières heures
 * Exemple: Si on est le 30 nov à 10h, on compte seulement depuis le 29 nov à 10h
 */
class SlidingWindow
{
    /**
     * Nettoyer les anciennes exécutions (plus de 24h)
     * = Désactiver les exécutions qui sont sorties de la fenêtre
     *
     * @param int $userId - L'utilisateur
     * @param int $applicationId - L'application
     * @return int - Nombre d'exécutions désactivées
     */
    public function nettoyerAnciennesExecutions(int $userId, int $applicationId): int
    {
        // 1. Calculer la date limite (il y a 24 heures)
        $dateLimite = Carbon::now()->subHours(24);

        // 2. Trouver les exécutions qui sont trop vieilles
        // (créées avant la date limite ET encore actives)
        $executionsADesactiver = DB::table('user_application_job')
            ->where('user_id', $userId)
            ->where('application_id', $applicationId)
            ->where('is_active', true)
            ->where('created_at', '<', $dateLimite)
            ->count();

        // 3. Désactiver ces exécutions (ne pas supprimer!)
        DB::table('user_application_job')
            ->where('user_id', $userId)
            ->where('application_id', $applicationId)
            ->where('is_active', true)
            ->where('created_at', '<', $dateLimite)
            ->update(['is_active' => false]);

        return $executionsADesactiver;
    }

    /**
     * Compter combien d'exécutions actives dans les 24 dernières heures
     *
     * @param int $userId - L'utilisateur
     * @param int $applicationId - L'application
     * @return int - Nombre d'exécutions actives dans la fenêtre
     */
    public function compterExecutionsActives(int $userId, int $applicationId): int
    {
        // 1. Calculer la date limite (il y a 24 heures)
        $dateLimite = Carbon::now()->subHours(24);

        // 2. Compter seulement les exécutions:
        //    - qui sont actives (is_active = true)
        //    - créées dans les 24 dernières heures
        return DB::table('user_application_job')
            ->where('user_id', $userId)
            ->where('application_id', $applicationId)
            ->where('is_active', true)
            ->where('created_at', '>=', $dateLimite)
            ->count();
    }

    /**
     * Vérifier si on peut créer une nouvelle exécution
     *
     * @param int $userId - L'utilisateur
     * @param int $applicationId - L'application
     * @param int $quotaMax - Le quota maximum (exemple: 100)
     * @return bool - true si on peut créer, false sinon
     */
    public function peutCreerNouvelleExecution(int $userId, int $applicationId, int $quotaMax): bool
    {
        // 1. Nettoyer d'abord les anciennes exécutions
        $this->nettoyerAnciennesExecutions($userId, $applicationId);

        // 2. Compter les exécutions actives
        $executionsActives = $this->compterExecutionsActives($userId, $applicationId);

        // 3. Vérifier si on est en dessous du quota
        return $executionsActives < $quotaMax;
    }

    /**
     * Obtenir des informations détaillées sur l'état de la fenêtre
     *
     * @param int $userId - L'utilisateur
     * @param int $applicationId - L'application
     * @param int $quotaMax - Le quota maximum
     * @return array - Informations détaillées
     */
    public function obtenirInfosFenetre(int $userId, int $applicationId, int $quotaMax): array
    {
        // 1. Nettoyer les anciennes
        $desactivees = $this->nettoyerAnciennesExecutions($userId, $applicationId);

        // 2. Compter les actives
        $actives = $this->compterExecutionsActives($userId, $applicationId);

        // 3. Calculer ce qui reste
        $restant = max(0, $quotaMax - $actives);

        return [
            'executions_actives' => $actives,
            'quota_maximum' => $quotaMax,
            'executions_restantes' => $restant,
            'executions_desactivees' => $desactivees,
            'peut_creer' => $actives < $quotaMax,
            'fenetre_depuis' => Carbon::now()->subHours(24)->toDateTimeString(),
            'fenetre_jusqua' => Carbon::now()->toDateTimeString(),
        ];
    }
}
