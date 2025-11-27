<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    $today = now()->startOfDay();

    // Récupérer les applications avec le nombre d'exécutions des dernières 24h
    $applications = \App\Models\Application::where('is_active', true)
        ->with(['licence.user', 'jobApplications.jobExecutions'])
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
                    'wording' => $app->licence->wording,
                    'user' => [
                        'name' => $app->licence->user->name,
                    ],
                ],
            ];
        });

    return Inertia::render('Dashboard', [
        'stats' => [
            'activeLicences' => \App\Models\Licence::where('status', 'ACTIVE')
                ->where('is_active', true)
                ->where('valid_to', '>=', now())
                ->count(),
            'totalApplications' => \App\Models\Application::where('is_active', true)->count(),
            'executionsToday' => \App\Models\JobExecution::where('created_at', '>=', $today)->count(),
        ],
        'applications' => $applications,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
