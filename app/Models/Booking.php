<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A customer order for one or more seats of a single screening.
 *
 * @property int $id
 * @property int $movie_session_id
 * @property string $customer_name
 * @property string $customer_email
 * @property string $total_amount
 * @property BookingStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read MovieSession $movieSession
 * @property-read Collection<int, BookingSeat> $bookingSeats
 * @property-read Collection<int, Seat> $seats
 */
#[Fillable(['movie_session_id', 'customer_name', 'customer_email', 'total_amount', 'status'])]
class Booking extends Model
{
    /**
     * Attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'status' => BookingStatus::class,
        ];
    }

    /**
     * The screening the booking was made for.
     *
     * @return BelongsTo<MovieSession, $this>
     */
    public function movieSession(): BelongsTo
    {
        return $this->belongsTo(MovieSession::class);
    }

    /**
     * Rows of the booking-seat pivot that belong to the booking.
     *
     * @return HasMany<BookingSeat, $this>
     */
    public function bookingSeats(): HasMany
    {
        return $this->hasMany(BookingSeat::class);
    }

    /**
     * Seats reserved by the booking.
     *
     * @return BelongsToMany<Seat, $this>
     */
    public function seats(): BelongsToMany
    {
        return $this->belongsToMany(Seat::class, 'booking_seats')->withTimestamps();
    }
}
