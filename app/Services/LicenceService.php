<?php

namespace App\Services;

use App\Models\Licence;
use App\Models\User;
use Illuminate\Support\Collection;

class LicenceService
{
    /**
     * Get all licences with users count
     */
    public function getAllWithUsersCount(): Collection
    {
        return Licence::withCount('users')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($licence) => $this->formatLicenceData($licence));
    }

    /**
     * Format licence data for display
     */
    public function formatLicenceData(Licence $licence): array
    {
        return [
            'id' => $licence->id,
            'uld' => $licence->uld,
            'wording' => $licence->wording,
            'slug' => $licence->slug,
            'description' => $licence->description,
            'max_apps' => $licence->max_apps,
            'max_executions_per_24h' => $licence->max_executions_per_24h,
            'valid_from' => $licence->valid_from,
            'valid_to' => $licence->valid_to,
            'status' => $licence->status,
            'is_active' => $licence->is_active,
            'users_count' => $licence->users_count ?? 0,
        ];
    }

    /**
     * Get licence statistics
     */
    public function getStatistics(): array
    {
        return [
            'totalLicences' => Licence::count(),
            'activeLicences' => $this->getActiveLicencesCount(),
            'totalUsers' => User::whereNotNull('licence_id')->count(),
        ];
    }

    /**
     * Get count of active licences
     */
    public function getActiveLicencesCount(): int
    {
        return Licence::where('status', 'ACTIVE')
            ->where('is_active', true)
            ->where('valid_to', '>=', now())
            ->count();
    }

    /**
     * Get available licences for upgrade (excluding admin licences)
     */
    public function getAvailableForUpgrade(): Collection
    {
        return Licence::where('is_active', true)
            ->where('status', 'ACTIVE')
            ->whereNotIn('wording', ['Admin', 'Administrateur'])
            ->get()
            ->map(fn($lic) => [
                'id' => $lic->id,
                'wording' => $lic->wording,
                'description' => $lic->description,
                'max_apps' => $lic->max_apps,
                'max_executions_per_24h' => $lic->max_executions_per_24h,
            ]);
    }

    /**
     * Create a new licence
     */
    public function create(array $data): Licence
    {
        return Licence::create([
            'wording' => $data['wording'],
            'description' => $data['description'] ?? null,
            'max_apps' => $data['max_apps'],
            'max_executions_per_24h' => $data['max_executions_per_24h'],
            'valid_from' => $data['valid_from'],
            'valid_to' => $data['valid_to'],
            'status' => $data['status'] ?? 'ACTIVE',
            'is_active' => $data['is_active'] ?? true,
            'is_custom' => $data['is_custom'] ?? false,
            'created_by_user_id' => $data['created_by_user_id'] ?? null,
        ]);
    }

    /**
     * Update an existing licence
     */
    public function update(Licence $licence, array $data): bool
    {
        return $licence->update([
            'wording' => $data['wording'],
            'description' => $data['description'] ?? null,
            'max_apps' => $data['max_apps'],
            'max_executions_per_24h' => $data['max_executions_per_24h'],
            'valid_from' => $data['valid_from'],
            'valid_to' => $data['valid_to'],
        ]);
    }

    /**
     * Toggle licence status between ACTIVE and SUSPENDED
     */
    public function toggleStatus(Licence $licence): string
    {
        $newStatus = $licence->status === 'ACTIVE' ? 'SUSPENDED' : 'ACTIVE';
        $licence->update(['status' => $newStatus]);

        return $newStatus;
    }

    /**
     * Toggle licence is_active flag
     */
    public function toggleActive(Licence $licence): bool
    {
        $newStatus = !$licence->is_active;
        $licence->update(['is_active' => $newStatus]);

        return $newStatus;
    }

    /**
     * Check if licence is available for use
     */
    public function isAvailable(Licence $licence): bool
    {
        return $licence->is_active && $licence->status === 'ACTIVE';
    }

    /**
     * Check if licence is admin licence
     */
    public function isAdminLicence(Licence $licence): bool
    {
        return in_array($licence->wording, ['Admin', 'Administrateur']);
    }
}
