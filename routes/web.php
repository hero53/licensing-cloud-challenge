<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LicenceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('dashboard/applications', [DashboardController::class, 'storeApplication'])->name('dashboard.applications.store');
    Route::post('dashboard/execute-job/{application}', [DashboardController::class, 'executeJob'])->name('dashboard.execute-job');
    Route::post('dashboard/upgrade-licence', [DashboardController::class, 'upgradeLicence'])->name('dashboard.upgrade-licence');

    Route::get('licences', [LicenceController::class, 'index'])->name('licences.index');
    Route::post('licences', [LicenceController::class, 'store'])->name('licences.store');
});

require __DIR__.'/settings.php';
