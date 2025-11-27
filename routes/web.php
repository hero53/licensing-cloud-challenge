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

    return Inertia::render('Dashboard', [
        'stats' => [
            'activeLicences' => \App\Models\Licence::where('status', 'ACTIVE')
                ->where('is_active', true)
                ->where('valid_to', '>=', now())
                ->count(),
            'totalApplications' => \App\Models\Application::where('is_active', true)->count(),
            'executionsToday' => \App\Models\JobExecution::where('created_at', '>=', $today)->count(),
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
