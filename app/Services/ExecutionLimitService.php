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
     * Uses license token (no SQL query) and sliding window
     */
    public function hasReachedExecutionLimit(User $user): bool
    {
        // Decode the token to get license info (no SQL query!)
        $licenceData = $this->tokenManager->getLicenceData($user->licence_token);

        // For each user's application, clean the sliding window
        $user->applications()->each(function ($application) use ($user) {
            $this->slidingWindow->nettoyerAnciennesExecutions($user->id, $application->id);
        });

        $executionsLast24h = $this->getExecutionsLast24h($user);
        return $executionsLast24h >= $licenceData->max_executions_per_24h;
    }

    /**
     * Check if user has reached application limit
     * Uses license token (no SQL query)
     */
    public function hasReachedApplicationLimit(User $user): bool
    {
        // Decode the token to get license info
        $licenceData = $this->tokenManager->getLicenceData($user->licence_token);

        $currentApplicationsCount = $user->applications()
            ->where('is_active', true)
            ->count();

        return $currentApplicationsCount >= $licenceData->max_apps;
    }

    /**
     * Get executions count in the last 24 hours
     * Only counts active executions (is_active = true)
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
     * Only counts active executions (is_active = true)
     */
    public function getExecutionsToday(User $user): int
    {
        return $user->userApplicationJobs()
            ->where('is_active', true)
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Get executions count for a specific application in the last 24h
     * Cleans the sliding window first, then counts active executions
     */
    public function getApplicationExecutionsLast24h(User $user, Application $application): int
    {
        // Clean the sliding window before counting
        $this->slidingWindow->nettoyerAnciennesExecutions($user->id, $application->id);

        return $user->userApplicationJobs()
            ->where('application_id', $application->id)
            ->where('is_active', true)
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }

    /**
     * Record a job execution
     * Cleans the sliding window BEFORE creating the new execution
     */
    public function recordExecution(User $user, Application $application, int $jobApplicationId): void
    {
        // 1. Clean the sliding window before creating the new execution
        $this->slidingWindow->nettoyerAnciennesExecutions($user->id, $application->id);

        // 2. Create the new execution (is_active will be automatically true by default)
        $user->userApplicationJobs()->create([
            'application_id' => $application->id,
            'job_application_id' => $jobApplicationId,
        ]);
    }

    /**
     * Get execution statistics for user
     * Uses license token
     */
    public function getExecutionStats(User $user): array
    {
        // Decode the token to get license info
        $licenceData = $this->tokenManager->getLicenceData($user->licence_token);

        return [
            'executionsToday' => $this->getExecutionsToday($user),
            'maxExecutionsPerDay' => $licenceData->max_executions_per_24h,
            'executionsLast24h' => $this->getExecutionsLast24h($user),
            'remainingExecutions' => max(0, $licenceData->max_executions_per_24h - $this->getExecutionsLast24h($user)),
        ];
    }

    /**
     * Clean all sliding windows for all user applications
     * Useful for a complete cleanup before displaying statistics
     */
    public function nettoyerToutesLesFenetres(User $user): int
    {
        $totalDesactive = 0;

        // For each user application
        $user->applications()->each(function ($application) use ($user, &$totalDesactive) {
            $nbDesactive = $this->slidingWindow->nettoyerAnciennesExecutions($user->id, $application->id);
            $totalDesactive += $nbDesactive;
        });

        return $totalDesactive;
    }

    /**
     * Simulate time advancement by deactivating all active executions
     * Debug method to test the sliding window mechanism
     */
    public function simulateTimeAdvancement(User $user): int
    {
        return \DB::table('user_application_job')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}
