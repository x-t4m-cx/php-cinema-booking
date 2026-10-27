<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use InvalidArgumentException;

/**
 * Accounts of the cinema panel: self-registration and staff management.
 */
class UserService
{
    /**
     * Register a customer. The role is always a buyer and never comes from the form.
     *
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function register(array $data): User
    {
        return $this->create($data, UserRole::Buyer);
    }

    /**
     * Create a staff account. Only the administrator may do this.
     *
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function createManager(array $data): User
    {
        return $this->create($data, UserRole::Manager);
    }

    /**
     * Change the role of a customer or a manager.
     *
     * @throws InvalidArgumentException when the administrator role is requested
     */
    public function changeRole(User $user, UserRole $role): User
    {
        if (! in_array($role, UserRole::assignable(), true)) {
            throw new InvalidArgumentException('Роль администратора нельзя назначить через панель.');
        }

        $user->update(['role' => $role]);

        return $user;
    }

    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    private function create(array $data, UserRole $role): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $role,
        ]);
    }
}
