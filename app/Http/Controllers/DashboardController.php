<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Services\ApplicationService;
use App\Services\ExecutionLimitService;
use App\Services\LicenceService;
use App\Services\LicenceUpgradeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private ApplicationService $applicationService,
        private ExecutionLimitService $executionLimitService,
        private LicenceService $licenceService,
        private LicenceUpgradeService $licenceUpgradeService,
        private \App\Core\LicenceTokenManager $tokenManager
    ) {}

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Décoder le token pour avoir les infos de la licence (pas de requête SQL!)
        $licenceData = $this->tokenManager->getLicenceData($user->licence_token);

        // Nettoyer toutes les fenêtres glissantes AVANT d'afficher les statistiques
        $this->executionLimitService->nettoyerToutesLesFenetres($user);

        $applications = $this->applicationService->getAccessibleApplications($user);
        $usedApplications = $this->applicationService->getAllAccessibleApplicationIds($user)->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'userLicence' => [
                    'wording' => $licenceData->wording,
                    'description' => $licenceData->description,
                    'validTo' => $licenceData->valid_to,
                ],
                'usedApplications' => $usedApplications,
                'maxApplications' => $licenceData->max_apps,
                'executionsToday' => $this->executionLimitService->getExecutionsToday($user),
                'maxExecutionsPerDay' => $licenceData->max_executions_per_24h,
            ],
            'applications' => $applications,
            'availableLicences' => $this->licenceService->getAvailableForUpgrade(),
            'currentLicenceId' => $user->licence_id,
            'hasCustomLicence' => $this->licenceUpgradeService->hasCustomLicence($user),
        ]);
    }

    public function executeJob(Application $application)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Décoder le token pour avoir les infos de la licence
        $licenceData = $this->tokenManager->getLicenceData($user->licence_token);

        if (!$this->applicationService->isActive($application)) {
            return back()->with('error', 'Cette application n\'est pas active.');
        }

        if (!$this->applicationService->belongsToUser($application, $user)) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à exécuter cette application.');
        }

        $jobApplication = $this->applicationService->getFirstJobApplication($application);

        if (!$jobApplication) {
            return back()->with('error', 'Aucun job disponible pour cette application.');
        }

        if ($this->executionLimitService->hasReachedExecutionLimit($user)) {
            return back()->with('error', 'Vous avez atteint la limite d\'exécutions autorisées par votre licence (' . $licenceData->max_executions_per_24h . ' par 24h). Veuillez upgrader votre licence pour continuer.');
        }

        $this->executionLimitService->recordExecution($user, $application, $jobApplication->id);

        return back()->with('success', 'Job exécuté avec succès pour ' . $application->wording);
    }

    public function storeApplication(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Décoder le token pour avoir les infos de la licence
        $licenceData = $this->tokenManager->getLicenceData($user->licence_token);

        if ($this->executionLimitService->hasReachedApplicationLimit($user)) {
            return back()->with('error', 'Vous avez atteint la limite d\'applications autorisées par votre licence (' . $licenceData->max_apps . ' applications).');
        }

        $validated = $request->validate([
            'wording' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $application = $this->applicationService->create($user, $validated);

        return back()->with('success', 'Application "' . $application->wording . '" créée avec succès !');
    }

    public function upgradeLicence(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $isCustom = $request->boolean('is_custom', false);

        if ($isCustom) {
            $validated = $request->validate([
                'wording' => 'required|string|max:255',
                'max_apps' => 'required|integer|min:1',
                'max_executions_per_24h' => 'required|integer|min:1',
            ]);

            $result = $this->licenceUpgradeService->upgradeToCustomLicence($user, $validated);
        } else {
            $validated = $request->validate([
                'licence_id' => 'required|exists:licences,id',
            ]);

            $result = $this->licenceUpgradeService->upgradeToPredefinedLicence($user, $validated['licence_id']);
        }

        $type = $result['success'] ? 'success' : 'error';
        return back()->with($type, $result['message']);
    }

    public function deleteApplication(Application $application)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$this->applicationService->belongsToUser($application, $user)) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à supprimer cette application.');
        }

        $this->applicationService->softDelete($application);

        return back()->with('success', 'L\'application "' . $application->wording . '" a été désactivée avec succès.');
    }

    /**
     * Désactiver toutes les exécutions actives (debug)
     * Simule le passage de 24h en mettant is_active = false
     */
    public function advanceTime()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Désactiver toutes les exécutions actives de cet utilisateur
        $deactivated = \DB::table('user_application_job')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return back()->with('success', "Fenêtre glissante simulée ! {$deactivated} exécutions désactivées");
    }
}
