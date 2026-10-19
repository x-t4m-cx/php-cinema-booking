<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A film that can be scheduled in the cinema.
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property int $duration_minutes
 * @property-read string $duration_label
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, MovieSession> $sessions
 */
#[Fillable(['title', 'description', 'duration_minutes'])]
class Movie extends Model
{
    /**
     * Human readable duration, for example "2 ч 15 мин".
     */
    protected function durationLabel(): Attribute
    {
        return Attribute::get(fn (): string => sprintf(
            '%d ч %02d мин',
            intdiv((int) $this->duration_minutes, 60),
            (int) $this->duration_minutes % 60,
        ));
    }

    /**
     * Screenings of this film.
     *
     * @return HasMany<MovieSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(MovieSession::class);
    }
}
