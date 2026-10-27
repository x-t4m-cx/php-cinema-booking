<?php

namespace App\Http\Controllers;

use App\Enums\MovieSessionStatus;
use App\Models\MovieSession;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Placeholder for choosing seats: booking and payment come later.
     */
    public function create(MovieSession $session): View
    {
        abort_if(
            $session->status === MovieSessionStatus::Cancelled || $session->isFinished(),
            404,
        );

        return view('bookings.create', [
            'session' => $session->load(['movie', 'hall']),
        ]);
    }
}
