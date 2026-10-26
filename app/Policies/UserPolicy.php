<?php

namespace App\Policies;

use App\Models\User;

/**
 * Who may manage the accounts of the cinema panel.
 *
 * The administrator sees the user list, creates managers and changes roles.
 * The administrator role itself is never granted or taken away here.
 */
class UserPolicy
{
    /**
     * Only the administrator opens the user list.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only the administrator opens a single account.
     */
    public function view(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only the administrator creates staff accounts.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only the administrator changes roles, and never the role of an administrator.
     */
    public function update(User $user, User $target): bool
    {
        return $user->isAdmin() && ! $target->isAdmin();
    }
}
