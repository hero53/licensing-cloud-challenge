<?php

namespace App\Http\Controllers;

use App\Models\Licence;
use App\Services\LicenceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LicenceController extends Controller
{
    public function __construct(
        private LicenceService $licenceService
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Licence::class);

        $licences = $this->licenceService->getAllWithUsersCount();
        $stats = $this->licenceService->getStatistics();

        return Inertia::render('Licences', [
            'stats' => $stats,
            'licences' => $licences,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Licence::class);

        $validated = $request->validate([
            'wording' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'max_apps' => 'required|integer|min:1',
            'max_executions_per_24h' => 'required|integer|min:1',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
        ]);

        $licence = $this->licenceService->create($validated);

        return back()->with('success', 'Licence "' . $licence->wording . '" créée avec succès !');
    }

    public function update(Request $request, Licence $licence)
    {
        $this->authorize('update', $licence);

        $validated = $request->validate([
            'wording' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'max_apps' => 'required|integer|min:1',
            'max_executions_per_24h' => 'required|integer|min:1',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
        ]);

        $this->licenceService->update($licence, $validated);

        return back()->with('success', 'Licence "' . $licence->wording . '" modifiée avec succès !');
    }

    public function toggleStatus(Licence $licence)
    {
        $this->authorize('update', $licence);

        $newStatus = $this->licenceService->toggleStatus($licence);
        $statusMessage = $newStatus === 'ACTIVE' ? 'activée' : 'suspendue';

        return back()->with('success', 'Licence "' . $licence->wording . '" ' . $statusMessage . ' avec succès !');
    }

    public function destroy(Licence $licence)
    {
        $this->authorize('delete', $licence);

        $newStatus = $this->licenceService->toggleActive($licence);
        $statusMessage = $newStatus ? 'activée' : 'désactivée';

        return back()->with('success', 'Licence "' . $licence->wording . '" ' . $statusMessage . ' avec succès !');
    }
}
