<?php

namespace App\Services;

use App\Enums\MovieSessionStatus;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\MovieSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Screening schedule of the cinema.
 *
 * A session occupies its hall from the start of the film until the film ends
 * plus the cleaning buffer, so two sessions in one hall can never overlap.
 * Cancelled sessions no longer occupy anything.
 */
class MovieSessionService
{
    /**
     * Create a session in an open hall for a film that is still in the show.
     *
     * @param  array{movie_id: int, hall_id: int, date: string, time: string, price: string}  $data
     *
     * @throws ValidationException
     */
    public function create(array $data): MovieSession
    {
        $movie = Movie::findOrFail($data['movie_id']);
        $hall = Hall::findOrFail($data['hall_id']);
        $startsAt = $this->startsAt($data['date'], $data['time']);

        $this->ensureStartsInFuture($startsAt);
        $this->ensureMovieCanBeScheduled($movie);
        $this->ensureHallIsOpen($hall);
        $this->ensureSlotIsFree($hall, $movie, $startsAt);

        return MovieSession::create([
            'movie_id' => $movie->id,
            'hall_id' => $hall->id,
            'starts_at' => $startsAt,
            'price' => $data['price'],
            'status' => MovieSessionStatus::Scheduled,
        ]);
    }

    /**
     * Move an active session to another time, film or hall.
     *
     * The session itself is not treated as a conflict with its own new time.
     *
     * @param  array{movie_id: int, hall_id: int, date: string, time: string, price: string}  $data
     *
     * @throws ValidationException
     */
    public function update(MovieSession $session, array $data): MovieSession
    {
        $movie = Movie::findOrFail($data['movie_id']);
        $hall = Hall::findOrFail($data['hall_id']);
        $startsAt = $this->startsAt($data['date'], $data['time']);

        $this->ensureStartsInFuture($startsAt);
        $this->ensureMovieCanBeScheduled($movie, $session);
        $this->ensureHallIsOpen($hall, $session);
        $this->ensureSlotIsFree($hall, $movie, $startsAt, $session);

        $session->update([
            'movie_id' => $movie->id,
            'hall_id' => $hall->id,
            'starts_at' => $startsAt,
            'price' => $data['price'],
        ]);

        return $session;
    }

    /**
     * Cancel a session so that its slot becomes available again.
     */
    public function cancel(MovieSession $session): MovieSession
    {
        if ($session->status !== MovieSessionStatus::Cancelled) {
            $session->update(['status' => MovieSessionStatus::Cancelled]);
        }

        return $session;
    }

    /**
     * Sessions of the hall that still occupy it on the given day.
     *
     * Sessions from the previous evening are included when they run past
     * midnight.
     *
     * @return Collection<int, MovieSession>
     */
    public function occupiedOn(Hall $hall, Carbon $day, ?MovieSession $ignore = null): Collection
    {
        $from = $day->copy()->startOfDay();
        $to = $from->copy()->addDay();

        return MovieSession::query()
            ->with('movie')
            ->where('hall_id', $hall->id)
            ->where('status', '!=', MovieSessionStatus::Cancelled->value)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))
            ->where('starts_at', '>=', $from->copy()->subDay())
            ->where('starts_at', '<', $to)
            ->get()
            ->filter(fn (MovieSession $session): bool => $session->busy_until->greaterThan($from))
            ->sortBy('starts_at')
            ->values();
    }

    /**
     * Public schedule of the given day: sessions that have not started yet.
     *
     * @return Collection<int, MovieSession>
     */
    public function scheduleOn(Carbon $date): Collection
    {
        return $this->publicSchedule()
            ->with(['movie', 'hall'])
            ->whereBetween('starts_at', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ])
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * All upcoming sessions from right now, used by the "all sessions" mode.
     *
     * @return Collection<int, MovieSession>
     */
    public function upcomingSchedule(): Collection
    {
        return $this->publicSchedule()
            ->with(['movie', 'hall'])
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * Whether the cinema has anything to show at all.
     */
    public function hasUpcoming(): bool
    {
        return $this->publicSchedule()->exists();
    }

    /**
     * Films that may be chosen on the session form: films in the show plus the
     * film of the session being edited, even if it has already been withdrawn.
     *
     * @return Collection<int, Movie>
     */
    public function selectableMovies(?int $keepId = null): Collection
    {
        return Movie::query()
            ->where(function ($query) use ($keepId): void {
                $query->where('is_active', true);

                if ($keepId !== null) {
                    $query->orWhereKey($keepId);
                }
            })
            ->orderBy('title')
            ->get();
    }

    /**
     * Halls that may be chosen on the session form: open halls plus the hall of
     * the session being edited, even if it has already been closed.
     *
     * @return Collection<int, Hall>
     */
    public function selectableHalls(?int $keepId = null): Collection
    {
        return Hall::query()
            ->where(function ($query) use ($keepId): void {
                $query->where('is_active', true);

                if ($keepId !== null) {
                    $query->orWhereKey($keepId);
                }
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * The session that already occupies the hall during the new time, if any.
     */
    private function findConflict(Hall $hall, Movie $movie, Carbon $startsAt, ?MovieSession $ignore = null): ?MovieSession
    {
        $busyUntil = $this->busyUntil($movie, $startsAt);

        /*
         * Films are at most ten hours long, so a session that started more than
         * a day earlier cannot reach the new start time.
         */
        return MovieSession::query()
            ->with('movie')
            ->where('hall_id', $hall->id)
            ->where('status', '!=', MovieSessionStatus::Cancelled->value)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))
            ->where('starts_at', '<', $busyUntil)
            ->where('starts_at', '>=', $busyUntil->copy()->subDay())
            ->get()
            ->first(fn (MovieSession $session): bool => $session->busy_until->greaterThan($startsAt));
    }

    /**
     * Sessions are never created in the past or a few minutes before now.
     *
     * @throws ValidationException
     */
    private function ensureStartsInFuture(Carbon $startsAt): void
    {
        if ($startsAt->greaterThanOrEqualTo(now()->addMinutes(MovieSession::MIN_LEAD_MINUTES))) {
            return;
        }

        throw ValidationException::withMessages([
            'time' => 'Начало сеанса должно быть не раньше, чем через '
                .MovieSession::MIN_LEAD_MINUTES.' минут.',
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function ensureMovieCanBeScheduled(Movie $movie, ?MovieSession $session = null): void
    {
        if ($movie->is_active || ($session !== null && $session->movie_id === $movie->id)) {
            return;
        }

        throw ValidationException::withMessages([
            'movie_id' => "Фильм «{$movie->title}» снят с показа: новые сеансы на него создать нельзя.",
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function ensureHallIsOpen(Hall $hall, ?MovieSession $session = null): void
    {
        if ($hall->is_active || ($session !== null && $session->hall_id === $hall->id)) {
            return;
        }

        throw ValidationException::withMessages([
            'hall_id' => "Зал «{$hall->name}» закрыт: новые сеансы в нём создать нельзя.",
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function ensureSlotIsFree(Hall $hall, Movie $movie, Carbon $startsAt, ?MovieSession $ignore = null): void
    {
        $conflict = $this->findConflict($hall, $movie, $startsAt, $ignore);

        if ($conflict === null) {
            return;
        }

        throw ValidationException::withMessages([
            'time' => sprintf(
                'Зал занят: «%s» идёт %s — %s, с учётом буфера %d минут зал освободится %s.',
                $conflict->movie->title,
                $conflict->starts_at->format('d.m H:i'),
                $conflict->ends_at->format('d.m H:i'),
                MovieSession::BUFFER_MINUTES,
                $conflict->busy_until->format('d.m H:i'),
            ),
        ]);
    }

    /**
     * Scheduled sessions that have not started yet.
     */
    private function publicSchedule(): Builder
    {
        return MovieSession::query()
            ->where('status', MovieSessionStatus::Scheduled->value)
            ->where('starts_at', '>=', now());
    }

    /**
     * Start of the new session as an application date-time.
     */
    private function startsAt(string $date, string $time): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}", config('app.timezone'));
    }

    /**
     * Moment the hall becomes free for the new movie at the new start time.
     */
    private function busyUntil(Movie $movie, Carbon $startsAt): Carbon
    {
        return $startsAt->copy()->addMinutes((int) $movie->duration_minutes + MovieSession::BUFFER_MINUTES);
    }
}
