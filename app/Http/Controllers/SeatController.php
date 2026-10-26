<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSeatRequest;
use App\Http\Requests\UpdateSeatRequest;
use App\Models\Hall;
use App\Models\Seat;
use App\Services\SeatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SeatController extends Controller
{
    public function __construct(private readonly SeatService $seats) {}

    /**
     * Form for a new seat of the hall.
     */
    public function create(Hall $hall): View
    {
        $this->authorize('create', Seat::class);

        return view('seats.create', [
            'hall' => $hall,
            'seat' => new Seat(['hall_id' => $hall->id, 'is_active' => true]),
        ]);
    }

    /**
     * Store a seat in the hall.
     */
    public function store(StoreSeatRequest $request, Hall $hall): RedirectResponse
    {
        $this->authorize('create', Seat::class);

        $seat = $this->seats->create($hall, $request->validated());

        return redirect()
            ->route('seats.show', $seat)
            ->with('status', "Место добавлено: {$seat->label}.");
    }

    /**
     * Single seat page.
     */
    public function show(Seat $seat): View
    {
        return view('seats.show', [
            'seat' => $seat->load(['hall', 'bookingSeats.booking.movieSession.movie']),
        ]);
    }

    /**
     * Form for editing a seat.
     */
    public function edit(Seat $seat): View
    {
        $this->authorize('update', $seat);

        return view('seats.edit', [
            'hall' => $seat->hall,
            'seat' => $seat,
        ]);
    }

    /**
     * Update a seat.
     */
    public function update(UpdateSeatRequest $request, Seat $seat): RedirectResponse
    {
        $this->authorize('update', $seat);

        $this->seats->update($seat, $request->validated());

        return redirect()
            ->route('seats.show', $seat)
            ->with('status', 'Место обновлено.');
    }

    /**
     * Delete a seat that is not booked.
     */
    public function destroy(Seat $seat): RedirectResponse
    {
        $this->authorize('delete', $seat);

        $hall = $seat->hall;

        $this->seats->delete($seat);

        return redirect()
            ->route('halls.show', $hall)
            ->with('status', 'Место удалено из зала.');
    }
}
