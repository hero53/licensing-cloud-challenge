<?php

namespace App\Http\Controllers;

use App\Models\Licence;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LicenceController extends Controller
{
    public function index()
    {
        $licences = Licence::withCount('users')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($licence) {
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
                    'users_count' => $licence->users_count,
                ];
            });

        $stats = [
            'totalLicences' => Licence::count(),
            'activeLicences' => Licence::where('status', 'ACTIVE')
                ->where('is_active', true)
                ->where('valid_to', '>=', now())
                ->count(),
            'totalUsers' => User::whereNotNull('licence_id')->count(),
        ];

        return Inertia::render('Licences', [
            'stats' => $stats,
            'licences' => $licences,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'wording' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'max_apps' => 'required|integer|min:1',
            'max_executions_per_24h' => 'required|integer|min:1',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
        ]);

        $licence = Licence::create([
            'wording' => $validated['wording'],
            'description' => $validated['description'] ?? null,
            'max_apps' => $validated['max_apps'],
            'max_executions_per_24h' => $validated['max_executions_per_24h'],
            'valid_from' => $validated['valid_from'],
            'valid_to' => $validated['valid_to'],
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        return back()->with('success', 'Licence "' . $licence->wording . '" créée avec succès !');
    }
}
