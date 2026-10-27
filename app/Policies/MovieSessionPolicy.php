<?php

namespace App\Policies;

use App\Enums\MovieSessionStatus;
use App\Models\MovieSession;
use App\Models\User;

/**
 * Who may build the screening schedule.
 *
 * Managers and the administrator create and cancel sessions; a cancelled
 * session cannot be edited again, it only frees its slot.
 */
class MovieSessionPolicy
{
    /**
     * The schedule is part of the staff panel.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * The schedule is part of the staff panel.
     */
    public function view(User $user, MovieSession $session): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Managers and the administrator add sessions.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Active sessions can be moved; a cancelled one is final.
     */
    public function update(User $user, MovieSession $session): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && $session->status !== MovieSessionStatus::Cancelled
            && ! $session->isFinished();
    }

    /**
     * Managers and the administrator cancel sessions.
     */
    public function cancel(User $user, MovieSession $session): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && ! $session->isFinished();
    }
}
