<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHallRequest;
use App\Http\Requests\UpdateHallRequest;
use App\Models\Hall;
use App\Services\HallService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HallController extends Controller
{
    public function __construct(private readonly HallService $halls) {}

    /**
     * List of halls.
     */
    public function index(): View
    {
        return view('halls.index', [
            'halls' => Hall::query()
                ->withCount(['seats', 'sessions'])
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    /**
     * Form for a new hall.
     */
    public function create(): View
    {
        $this->authorize('create', Hall::class);

        return view('halls.create', [
            'hall' => new Hall,
        ]);
    }

    /**
     * Store a hall and generate its seat grid.
     */
    public function store(StoreHallRequest $request): RedirectResponse
    {
        $this->authorize('create', Hall::class);

        $hall = $this->halls->create($request->validated());

        return redirect()
            ->route('halls.show', $hall)
            ->with('status', "Зал «{$hall->name}» создан, места сгенерированы.");
    }

    /**
     * Hall page with the seat grid.
     */
    public function show(Hall $hall): View
    {
        return view('halls.show', [
            'hall' => $hall->loadCount('sessions'),
            'grid' => $this->halls->seatGrid($hall),
            'stats' => $this->halls->seatStats($hall),
        ]);
    }

    /**
     * Form for editing a hall.
     */
    public function edit(Hall $hall): View
    {
        $this->authorize('update', $hall);

        return view('halls.edit', [
            'hall' => $hall,
        ]);
    }

    /**
     * Update a hall and resize its seat grid.
     */
    public function update(UpdateHallRequest $request, Hall $hall): RedirectResponse
    {
        $this->authorize('update', $hall);

        $this->halls->update($hall, $request->validated());

        return redirect()
            ->route('halls.show', $hall)
            ->with('status', 'Зал обновлён, сетка мест синхронизирована.');
    }

    /**
     * Delete a hall without bookings.
     */
    public function destroy(Hall $hall): RedirectResponse
    {
        $this->authorize('delete', $hall);

        $this->halls->delete($hall);

        return redirect()
            ->route('halls.index')
            ->with('status', "Зал «{$hall->name}» удалён.");
    }
}
