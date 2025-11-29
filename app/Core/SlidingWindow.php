<?php

namespace App\Core;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class to manage the 24-hour sliding window
 *
 * Simple explanation:
 * A 24h sliding window = we only count executions from the last 24 hours
 * Example: If it's Nov 30 at 10am, we only count since Nov 29 at 10am
 */
class SlidingWindow
{
    /**
     * Clean old executions (older than 24h)
     * = Deactivate executions that are outside the window
     *
     * @param int $userId - The user
     * @param int $applicationId - The application
     * @return int - Number of deactivated executions
     */
    public function nettoyerAnciennesExecutions(int $userId, int $applicationId): int
    {
        // 1. Calculate the cutoff date (24 hours ago)
        $dateLimite = Carbon::now()->subHours(24);

        // 2. Find executions that are too old
        // (created before cutoff date AND still active)
        $executionsADesactiver = DB::table('user_application_job')
            ->where('user_id', $userId)
            ->where('application_id', $applicationId)
            ->where('is_active', true)
            ->where('created_at', '<', $dateLimite)
            ->count();

        // 3. Deactivate these executions (do not delete!)
        DB::table('user_application_job')
            ->where('user_id', $userId)
            ->where('application_id', $applicationId)
            ->where('is_active', true)
            ->where('created_at', '<', $dateLimite)
            ->update(['is_active' => false]);

        return $executionsADesactiver;
    }

    /**
     * Count active executions in the last 24 hours
     *
     * @param int $userId - The user
     * @param int $applicationId - The application
     * @return int - Number of active executions in the window
     */
    public function compterExecutionsActives(int $userId, int $applicationId): int
    {
        // 1. Calculate the cutoff date (24 hours ago)
        $dateLimite = Carbon::now()->subHours(24);

        // 2. Count only executions that are:
        //    - active (is_active = true)
        //    - created in the last 24 hours
        return DB::table('user_application_job')
            ->where('user_id', $userId)
            ->where('application_id', $applicationId)
            ->where('is_active', true)
            ->where('created_at', '>=', $dateLimite)
            ->count();
    }

    /**
     * Check if a new execution can be created
     *
     * @param int $userId - The user
     * @param int $applicationId - The application
     * @param int $quotaMax - The maximum quota (example: 100)
     * @return bool - true if can create, false otherwise
     */
    public function peutCreerNouvelleExecution(int $userId, int $applicationId, int $quotaMax): bool
    {
        // 1. Clean old executions first
        $this->nettoyerAnciennesExecutions($userId, $applicationId);

        // 2. Count active executions
        $executionsActives = $this->compterExecutionsActives($userId, $applicationId);

        // 3. Check if below quota
        return $executionsActives < $quotaMax;
    }

    /**
     * Get detailed information about the window state
     *
     * @param int $userId - The user
     * @param int $applicationId - The application
     * @param int $quotaMax - The maximum quota
     * @return array - Detailed information
     */
    public function obtenirInfosFenetre(int $userId, int $applicationId, int $quotaMax): array
    {
        // 1. Clean old executions
        $desactivees = $this->nettoyerAnciennesExecutions($userId, $applicationId);

        // 2. Count active executions
        $actives = $this->compterExecutionsActives($userId, $applicationId);

        // 3. Calculate remaining quota
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
