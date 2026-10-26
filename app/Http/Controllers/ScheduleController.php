<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\MovieSession;
use App\Services\MovieSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Public poster: the sessions any visitor can see.
 */
class ScheduleController extends Controller
{
    public function __construct(private readonly MovieSessionService $sessions) {}

    /**
     * Schedule for one day or for all upcoming days.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $today = now()->startOfDay();
        $dateParam = $request->query('date');
        $dateParam = is_string($dateParam) ? $dateParam : null;

        $showAll = $dateParam === 'all';
        $selected = null;

        if (! $showAll) {
            $selected = $today->copy();

            if ($dateParam !== null && $dateParam !== '') {
                if (! Carbon::hasFormat($dateParam, 'Y-m-d')) {
                    return redirect()->route('schedule.index');
                }

                $selected = Carbon::createFromFormat('Y-m-d', $dateParam)->startOfDay();

                if ($selected->lt($today)) {
                    return redirect()->route('schedule.index', ['date' => $today->toDateString()]);
                }
            }
        }

        $stripStart = $selected === null
            ? $today->copy()
            : $selected->copy()->subDays(6);

        if ($stripStart->lt($today)) {
            $stripStart = $today->copy();
        }

        $days = collect(range(0, 6))
            ->map(fn (int $offset): Carbon => $stripStart->copy()->addDays($offset));

        $previousDate = $selected?->copy()->subDays(7);
        if ($previousDate !== null && $previousDate->lt($today)) {
            $previousDate = $today->copy();
        }

        $hasUpcoming = $this->sessions->hasUpcoming();
        $groups = collect();
        $dayGroups = collect();

        if ($showAll) {
            $dayGroups = $this->sessions->upcomingSchedule()
                ->groupBy(fn (MovieSession $session): string => $session->starts_at->toDateString())
                ->map(fn (Collection $items): array => [
                    'date' => $items->first()->starts_at->copy()->startOfDay(),
                    'groups' => $this->groupByMovie($items),
                ])
                ->values();
        } else {
            $groups = $this->groupByMovie($this->sessions->scheduleOn($selected));
        }

        return view('schedule.index', [
            'showAll' => $showAll,
            'selected' => $selected,
            'days' => $days,
            'previousDate' => $previousDate,
            'nextDate' => $selected?->copy()->addDays(7),
            'hasUpcoming' => $hasUpcoming,
            'groups' => $groups,
            'dayGroups' => $dayGroups,
        ]);
    }

    /**
     * Sessions grouped by film, ordered by the first session of each film.
     *
     * @param  Collection<int, MovieSession>  $sessions
     * @return Collection<int, array{movie: Movie, sessions: Collection<int, MovieSession>}>
     */
    private function groupByMovie(Collection $sessions): Collection
    {
        return $sessions
            ->groupBy('movie_id')
            ->map(fn (Collection $items): array => [
                'movie' => $items->first()->movie,
                'sessions' => $items->values(),
            ])
            ->values();
    }
}
