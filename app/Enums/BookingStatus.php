<?php

namespace App\Enums;

/**
 * Lifecycle of a customer booking.
 */
enum BookingStatus: string
{
    /** Created, seats reserved, not paid yet. */
    case Pending = 'pending';

    /** Paid and confirmed. */
    case Confirmed = 'confirmed';

    /** Cancelled by the customer or the cinema; seats are released. */
    case Cancelled = 'cancelled';
}
