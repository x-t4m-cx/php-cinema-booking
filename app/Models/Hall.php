<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * A cinema hall with a fixed seat grid.
 *
 * @property int $id
 * @property string $name
 * @property int $rows_count
 * @property int $seats_per_rows
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Seat> $seats
 * @property-read Collection<int, MovieSession> $sessions
 * @property-read Collection<int, Booking> $bookings
 */
#[Fillable(['name', 'rows_count', 'seats_per_rows', 'is_active'])]
class Hall extends Model
{
    /**
     * Attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Seats that belong to the hall.
     *
     * @return HasMany<Seat, $this>
     */
    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    /**
     * Screenings hosted in the hall.
     *
     * @return HasMany<MovieSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(MovieSession::class);
    }

    /**
     * Bookings made for screenings in the hall.
     *
     * @return HasManyThrough<Booking, MovieSession, $this>
     */
    public function bookings(): HasManyThrough
    {
        return $this->hasManyThrough(Booking::class, MovieSession::class);
    }
}
