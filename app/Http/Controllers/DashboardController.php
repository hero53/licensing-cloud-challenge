<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobApplication;
use App\Models\Licence;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $licence = $user->licence;

        // Récupérer les applications actives accessibles par l'utilisateur
        // Soit les applications qu'il possède, soit celles auxquelles il a accès via user_application_job
        $ownedApplicationIds = $user->applications()->pluck('id');
        $authorizedApplicationIds = $user->authorizedApplications()->pluck('applications.id');
        $allApplicationIds = $ownedApplicationIds->merge($authorizedApplicationIds)->unique();

        $applications = collect();

        if ($allApplicationIds->isNotEmpty()) {
            $applications = Application::whereIn('id', $allApplicationIds)
                ->where('is_active', true)
                ->with(['jobApplications', 'user.licence'])
                ->get();
        }

        $applications = $applications->map(function ($app) use ($user) {
                // Compter les exécutions des dernières 24h pour cette application
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
            });

        // Compter le nombre d'applications utilisées par l'utilisateur
        $usedApplications = $allApplicationIds->count();

        // Compter le nombre d'exécutions aujourd'hui
        $executionsToday = $user->userApplicationJobs()
            ->whereDate('created_at', today())
            ->count();

        // Récupérer toutes les licences disponibles pour l'upgrade (exclure Admin uniquement)
        $availableLicences = Licence::where('is_active', true)
            ->where('status', 'ACTIVE')
            ->whereNotIn('wording', ['Admin', 'Administrateur'])
            ->get()
            ->map(function ($lic) {
                return [
                    'id' => $lic->id,
                    'wording' => $lic->wording,
                    'description' => $lic->description,
                    'max_apps' => $lic->max_apps,
                    'max_executions_per_24h' => $lic->max_executions_per_24h,
                ];
            });

        // Vérifier si l'utilisateur a déjà créé une licence personnalisée
        $hasCustomLicence = Licence::where('created_by_user_id', $user->id)
            ->where('is_custom', true)
            ->exists();

        return Inertia::render('Dashboard', [
            'stats' => [
                'userLicence' => [
                    'wording' => $licence->wording,
                    'description' => $licence->description,
                    'validTo' => $licence->valid_to,
                ],
                'usedApplications' => $usedApplications,
                'maxApplications' => $licence->max_apps,
                'executionsToday' => $executionsToday,
                'maxExecutionsPerDay' => $licence->max_executions_per_24h,
            ],
            'applications' => $applications,
            'availableLicences' => $availableLicences,
            'currentLicenceId' => $user->licence_id,
            'hasCustomLicence' => $hasCustomLicence,
        ]);
    }

    public function executeJob(Application $application)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $licence = $user->licence;

        // Vérifier que l'application est active
        if (!$application->is_active) {
            return back()->with('error', 'Cette application n\'est pas active.');
        }

        // Vérifier que l'application appartient à l'utilisateur
        if ($application->user_id !== $user->id) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à exécuter cette application.');
        }

        // Vérifier que l'application a au moins un JobApplication
        $jobApplication = $application->jobApplications()->first();

        if (!$jobApplication) {
            return back()->with('error', 'Aucun job disponible pour cette application.');
        }

        // Vérifier la limite d'exécutions par 24h
        $executionsLast24h = $user->userApplicationJobs()
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($executionsLast24h >= $licence->max_executions_per_24h) {
            return back()->with('error', 'Vous avez atteint la limite d\'exécutions autorisées par votre licence (' . $licence->max_executions_per_24h . ' par 24h). Veuillez upgrader votre licence pour continuer.');
        }

        // Enregistrer l'exécution
        $user->userApplicationJobs()->create([
            'application_id' => $application->id,
            'job_application_id' => $jobApplication->id,
        ]);

        return back()->with('success', 'Job exécuté avec succès pour ' . $application->wording);
    }

    public function storeApplication(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $licence = $user->licence;

        // Vérifier que l'utilisateur n'a pas atteint la limite d'applications
        $currentApplicationsCount = $user->applications()->where('is_active', true)->count();

        if ($currentApplicationsCount >= $licence->max_apps) {
            return back()->with('error', 'Vous avez atteint la limite d\'applications autorisées par votre licence (' . $licence->max_apps . ' applications).');
        }

        // Valider les données
        $validated = $request->validate([
            'wording' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Créer l'application
        $application = $user->applications()->create([
            'wording' => $validated['wording'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        // Créer un JobApplication par défaut pour cette application
        JobApplication::create([
            'application_id' => $application->id,
            'wording' => 'Default Job for ' . $application->wording,
        ]);

        return back()->with('success', 'Application "' . $application->wording . '" créée avec succès !');
    }

    public function upgradeLicence(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $isCustom = $request->boolean('is_custom', false);

        if ($isCustom) {
            // Vérifier si l'utilisateur a déjà créé une licence personnalisée
            $existingCustomLicence = Licence::where('created_by_user_id', $user->id)
                ->where('is_custom', true)
                ->first();

            if ($existingCustomLicence) {
                return back()->with('error', 'Vous avez déjà créé une licence personnalisée. Chaque utilisateur ne peut créer qu\'une seule licence personnalisée.');
            }

            // Créer une licence personnalisée
            $validated = $request->validate([
                'wording' => 'required|string|max:255',
                'max_apps' => 'required|integer|min:1',
                'max_executions_per_24h' => 'required|integer|min:1',
            ]);

            // Créer la nouvelle licence personnalisée
            $newLicence = Licence::create([
                'wording' => $validated['wording'],
                'description' => 'Licence personnalisée pour ' . $user->name,
                'max_apps' => $validated['max_apps'],
                'max_executions_per_24h' => $validated['max_executions_per_24h'],
                'valid_from' => now(),
                'valid_to' => now()->addYear(),
                'status' => 'ACTIVE',
                'is_active' => true,
                'is_custom' => true,
                'created_by_user_id' => $user->id,
            ]);

            // Mettre à jour la licence de l'utilisateur
            $user->update([
                'licence_id' => $newLicence->id,
            ]);

            return back()->with('success', 'Votre licence personnalisée "' . $newLicence->wording . '" a été créée et activée avec succès !');
        } else {
            // Upgrader vers une licence prédéfinie
            $validated = $request->validate([
                'licence_id' => 'required|exists:licences,id',
            ]);

            $newLicence = Licence::findOrFail($validated['licence_id']);

            // Vérifier que la nouvelle licence n'est pas "Admin"
            if (in_array($newLicence->wording, ['Admin', 'Administrateur'])) {
                return back()->with('error', 'Vous ne pouvez pas upgrader vers une licence Admin.');
            }

            // Vérifier que la nouvelle licence est active
            if (!$newLicence->is_active || $newLicence->status !== 'ACTIVE') {
                return back()->with('error', 'Cette licence n\'est pas disponible.');
            }

            // Mettre à jour la licence de l'utilisateur
            $user->update([
                'licence_id' => $newLicence->id,
            ]);

            return back()->with('success', 'Votre licence a été upgradée vers "' . $newLicence->wording . '" avec succès !');
        }
    }

    public function deleteApplication(Application $application)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Vérifier que l'application appartient à l'utilisateur
        if ($application->user_id !== $user->id) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à supprimer cette application.');
        }

        // Suppression logique : mettre is_active à false
        $application->update([
            'is_active' => false,
        ]);

        return back()->with('success', 'L\'application "' . $application->wording . '" a été désactivée avec succès.');
    }
}
