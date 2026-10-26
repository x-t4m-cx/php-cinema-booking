<?php

namespace App\Policies;

use App\Models\Movie;
use App\Models\User;

/**
 * Who may change the film catalogue.
 *
 * Viewing is open to everyone, a manager adds new films, and only the
 * administrator edits or removes them.
 */
class MoviePolicy
{
    /**
     * Anyone, including a guest, may browse the catalogue.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Anyone may open a film card.
     */
    public function view(?User $user, Movie $movie): bool
    {
        return true;
    }

    /**
     * The administrator and managers add films to the catalogue.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Only the administrator changes an existing film.
     */
    public function update(User $user, Movie $movie): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only the administrator removes films.
     */
    public function delete(User $user, Movie $movie): bool
    {
        return $user->isAdmin();
    }
}
