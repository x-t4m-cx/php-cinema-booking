<?php

namespace App\Enums;

/**
 * Lifecycle of a single screening (movie session) in a hall.
 */
enum MovieSessionStatus: string
{
    /** Scheduled and visible for booking. */
    case Scheduled = 'scheduled';

    /** Cancelled by the cinema; existing bookings are refunded. */
    case Cancelled = 'cancelled';

    /**
     * Human readable status for the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'активен',
            self::Cancelled => 'отменён',
        };
    }
}
