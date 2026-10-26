<?php

namespace App\Enums;

/**
 * Access level of a user in the cinema panel.
 *
 * The administrator manages everything, a manager only adds films (and
 * screenings once they are implemented), while a buyer may only view the
 * catalogue for now, with booking and payment planned for later.
 */
enum UserRole: string
{
    /** Full access to films, halls and seats. */
    case Admin = 'admin';

    /** May add films and, once they exist, screenings. */
    case Manager = 'manager';

    /** May view the catalogue and, once it exists, book and pay for seats. */
    case Buyer = 'buyer';

    /**
     * Human readable role name for the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Администратор',
            self::Manager => 'Менеджер',
            self::Buyer => 'Покупатель',
        };
    }

    /**
     * Roles an administrator may grant through the panel.
     *
     * The administrator role is never assignable from the interface.
     *
     * @return list<self>
     */
    public static function assignable(): array
    {
        return [self::Buyer, self::Manager];
    }
}
