<?php

namespace App\Services;

use App\Models\Application;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Support\Collection;

class ApplicationService
{
    /**
     * Get all applications accessible by user (owned + authorized)
     */
    public function getAccessibleApplications(User $user): Collection
    {
        $allApplicationIds = $this->getAllAccessibleApplicationIds($user);

        if ($allApplicationIds->isEmpty()) {
            return collect();
        }

        return Application::whereIn('id', $allApplicationIds)
            ->where('is_active', true)
            ->with(['jobApplications', 'user.licence'])
            ->get()
            ->map(fn($app) => $this->formatApplicationData($app, $user));
    }

    /**
     * Get all application IDs accessible by user
     */
    public function getAllAccessibleApplicationIds(User $user): Collection
    {
        $ownedApplicationIds = $user->applications()->pluck('id');
        $authorizedApplicationIds = $user->authorizedApplications()->pluck('applications.id');

        return $ownedApplicationIds->merge($authorizedApplicationIds)->unique();
    }

    /**
     * Format application data for display
     */
    public function formatApplicationData(Application $app, User $user): array
    {
        $executions24h = $user->userApplicationJobs()
            ->where('application_id', $app->id)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return [
            'id' => $app->id,
            'uld' => $app->uld,
            'wording' => $app->wording,
            'slug' => $app->slug,
            'description' => $app->description,
            'executions_24h' => $executions24h,
            'licence' => [
                'wording' => $app->user->licence->wording,
                'user' => [
                    'name' => $app->user->name,
                ],
            ],
        ];
    }

    /**
     * Create a new application for user
     */
    public function create(User $user, array $data): Application
    {
        $application = $user->applications()->create([
            'wording' => $data['wording'],
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);

        // Create default JobApplication
        JobApplication::create([
            'application_id' => $application->id,
            'wording' => 'Default Job for ' . $application->wording,
        ]);

        return $application;
    }

    /**
     * Soft delete application
     */
    public function softDelete(Application $application): bool
    {
        return $application->update(['is_active' => false]);
    }

    /**
     * Get user's active applications count
     */
    public function getActiveApplicationsCount(User $user): int
    {
        return $user->applications()->where('is_active', true)->count();
    }

    /**
     * Check if application belongs to user
     */
    public function belongsToUser(Application $application, User $user): bool
    {
        return $application->user_id === $user->id;
    }

    /**
     * Check if application is active
     */
    public function isActive(Application $application): bool
    {
        return $application->is_active;
    }

    /**
     * Get first job application for an application
     */
    public function getFirstJobApplication(Application $application): ?JobApplication
    {
        return $application->jobApplications()->first();
    }
}
