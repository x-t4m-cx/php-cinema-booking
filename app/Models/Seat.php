<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A single place in a hall, identified by row and seat number.
 *
 * @property int $id
 * @property int $hall_id
 * @property int $row_number
 * @property int $seat_number
 * @property bool $is_active
 * @property-read string $label
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Hall $hall
 * @property-read Collection<int, BookingSeat> $bookingSeats
 */
#[Fillable(['hall_id', 'row_number', 'seat_number', 'is_active'])]
class Seat extends Model
{
    /**
     * Human readable position, for example "Ряд 3, место 12".
     */
    protected function label(): Attribute
    {
        return Attribute::get(fn (): string => "Ряд {$this->row_number}, место {$this->seat_number}");
    }

    /**
     * Attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'row_number' => 'integer',
            'seat_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The hall the seat belongs to.
     *
     * @return BelongsTo<Hall, $this>
     */
    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    /**
     * Rows of the booking-seat pivot that reference this seat.
     *
     * @return HasMany<BookingSeat, $this>
     */
    public function bookingSeats(): HasMany
    {
        return $this->hasMany(BookingSeat::class);
    }
}
