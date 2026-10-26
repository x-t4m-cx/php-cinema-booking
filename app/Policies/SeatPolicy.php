<?php

namespace App\Policies;

use App\Models\Seat;
use App\Models\User;

/**
 * Who may change single seats of a hall.
 *
 * The seat grid belongs to the hall, so it is also maintained by the
 * administrator only.
 */
class SeatPolicy
{
    /**
     * Anyone may look at the seats through the hall grid.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Anyone may open a seat card.
     */
    public function view(?User $user, Seat $seat): bool
    {
        return true;
    }

    /**
     * Only the administrator adds seats.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only the administrator moves or disables a seat.
     */
    public function update(User $user, Seat $seat): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only the administrator removes seats.
     */
    public function delete(User $user, Seat $seat): bool
    {
        return $user->isAdmin();
    }
}
