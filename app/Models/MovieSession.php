<?php

namespace App\Models;

use App\Enums\MovieSessionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A screening of a film in a hall at a concrete time.
 *
 * @property int $id
 * @property int $movie_id
 * @property int $hall_id
 * @property Carbon $starts_at
 * @property string $price
 * @property MovieSessionStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Carbon $ends_at
 * @property-read Carbon $busy_until
 * @property-read Movie $movie
 * @property-read Hall $hall
 * @property-read Collection<int, Booking> $bookings
 */
#[Fillable(['movie_id', 'hall_id', 'starts_at', 'price', 'status'])]
class MovieSession extends Model
{
    /**
     * Minutes reserved between sessions for cleaning the hall and seating the
     * next audience.
     */
    public const BUFFER_MINUTES = 20;

    /**
     * Minimum time before the start when a new session may be scheduled.
     */
    public const MIN_LEAD_MINUTES = 15;

    /**
     * Whether the session has already ended.
     *
     * Finished sessions stay in the history but are inactive: they are not
     * shown to buyers and cannot be edited or cancelled.
     */
    public function isFinished(): bool
    {
        return $this->ends_at->isPast();
    }

    /**
     * End of the film: the start plus the duration taken from the film.
     */
    protected function endsAt(): Attribute
    {
        return Attribute::get(fn (): Carbon => $this->starts_at->copy()
            ->addMinutes((int) $this->movie->duration_minutes));
    }

    /**
     * Moment the hall is free again: the end of the film plus the buffer.
     */
    protected function busyUntil(): Attribute
    {
        return Attribute::get(fn (): Carbon => $this->ends_at->copy()
            ->addMinutes(self::BUFFER_MINUTES));
    }

    /**
     * Attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'price' => 'decimal:2',
            'status' => MovieSessionStatus::class,
        ];
    }

    /**
     * The film being screened.
     *
     * @return BelongsTo<Movie, $this>
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * The hall the film is screened in.
     *
     * @return BelongsTo<Hall, $this>
     */
    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    /**
     * Bookings made for this screening.
     *
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
