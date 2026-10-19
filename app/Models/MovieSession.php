<?php

namespace App\Models;

use App\Enums\MovieSessionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
 * @property Carbon $ends_at
 * @property string $price
 * @property MovieSessionStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Movie $movie
 * @property-read Hall $hall
 * @property-read Collection<int, Booking> $bookings
 */
#[Fillable(['movie_id', 'hall_id', 'starts_at', 'ends_at', 'price', 'status'])]
class MovieSession extends Model
{
    /**
     * Attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
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
