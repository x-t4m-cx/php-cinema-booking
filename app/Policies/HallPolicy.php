<?php

namespace App\Policies;

use App\Models\Hall;
use App\Models\User;

/**
 * Who may change the halls of the cinema.
 *
 * Halls and their seat grids are maintained by the administrator only.
 */
class HallPolicy
{
    /**
     * Anyone may browse the halls.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Anyone may open a hall and look at its seat grid.
     */
    public function view(?User $user, Hall $hall): bool
    {
        return true;
    }

    /**
     * Only the administrator creates halls.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only the administrator resizes a hall.
     */
    public function update(User $user, Hall $hall): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only the administrator removes halls.
     */
    public function delete(User $user, Hall $hall): bool
    {
        return $user->isAdmin();
    }
}
