<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobExecution;
use App\Models\Licence;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();

        // Récupérer les applications avec le nombre d'exécutions des dernières 24h
        $applications = Application::where('is_active', true)
            ->with(['user.licence', 'jobApplications.jobExecutions'])
            ->get()
            ->map(function ($app) {
                // Compter les exécutions des dernières 24h
                $executions24h = $app->jobApplications->sum(function ($jobApp) {
                    return $jobApp->jobExecutions->where('created_at', '>=', now()->subDay())->count();
                });

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

        return Inertia::render('Dashboard', [
            'stats' => [
                'activeLicences' => Licence::where('status', 'ACTIVE')
                    ->where('is_active', true)
                    ->where('valid_to', '>=', now())
                    ->count(),
                'totalApplications' => Application::where('is_active', true)->count(),
                'executionsToday' => JobExecution::where('created_at', '>=', $today)->count(),
            ],
            'applications' => $applications,
        ]);
    }

    public function executeJob(Request $request, Application $application)
    {
        // Vérifier que l'application est active
        if (!$application->is_active) {
            return back()->with('error', 'Cette application n\'est pas active.');
        }

        // Vérifier que l'application a au moins un JobApplication
        $jobApplication = $application->jobApplications()->first();

        if (!$jobApplication) {
            return back()->with('error', 'Aucun job disponible pour cette application.');
        }

        // Créer une nouvelle exécution de job
        JobExecution::create([
            'job_application_id' => $jobApplication->id,
            'user_id' => auth()->user()->id,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Job exécuté avec succès pour ' . $application->wording);
    }
}
