<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovieSessionRequest;
use App\Http\Requests\UpdateMovieSessionRequest;
use App\Models\Hall;
use App\Models\MovieSession;
use App\Services\MovieSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MovieSessionController extends Controller
{
    public function __construct(private readonly MovieSessionService $sessions) {}

    /**
     * Screening schedule.
     */
    public function index(): View
    {
        $this->authorize('viewAny', MovieSession::class);

        return view('sessions.index', [
            'sessions' => MovieSession::query()
                ->with(['movie', 'hall'])
                ->orderByDesc('starts_at')
                ->paginate(10),
        ]);
    }

    /**
     * Form for a new session.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', MovieSession::class);

        return view('sessions.create', $this->formData(new MovieSession, $request));
    }

    /**
     * Store a new session.
     */
    public function store(StoreMovieSessionRequest $request): RedirectResponse
    {
        $this->authorize('create', MovieSession::class);

        $session = $this->sessions->create($request->validated());

        return redirect()
            ->route('sessions.index')
            ->with('status', "Сеанс «{$session->movie->title}» в зале «{$session->hall->name}» на {$session->starts_at->format('d.m H:i')} создан.");
    }

    /**
     * Form for editing a session.
     */
    public function edit(Request $request, MovieSession $session): View
    {
        $this->authorize('update', $session);

        return view('sessions.edit', $this->formData($session, $request));
    }

    /**
     * Update a session. The session does not conflict with itself.
     */
    public function update(UpdateMovieSessionRequest $request, MovieSession $session): RedirectResponse
    {
        $this->authorize('update', $session);

        $this->sessions->update($session, $request->validated());

        return redirect()
            ->route('sessions.index')
            ->with('status', 'Сеанс обновлён.');
    }

    /**
     * Cancel a session and free its slot.
     */
    public function cancel(MovieSession $session): RedirectResponse
    {
        $this->authorize('cancel', $session);

        $this->sessions->cancel($session);

        return redirect()
            ->route('sessions.index')
            ->with('status', 'Сеанс отменён, слот освобождён для новых сеансов.');
    }

    /**
     * Busy intervals of a hall on a day, used by the session form.
     */
    public function occupied(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MovieSession::class);

        $data = $request->validate([
            'hall_id' => ['required', 'integer', Rule::exists('halls', 'id')],
            'date' => ['required', 'date_format:Y-m-d'],
            'session_id' => ['nullable', 'integer', Rule::exists('movie_sessions', 'id')],
        ]);

        $hall = Hall::findOrFail($data['hall_id']);
        $day = Carbon::createFromFormat('Y-m-d', $data['date'])->startOfDay();
        $ignore = isset($data['session_id']) ? MovieSession::find($data['session_id']) : null;

        return response()->json([
            'hall' => $hall->name,
            'date_label' => $day->format('d.m.Y'),
            'buffer' => MovieSession::BUFFER_MINUTES,
            'occupied' => $this->sessions->occupiedOn($hall, $day, $ignore)
                ->map(fn (MovieSession $session): array => [
                    'id' => $session->id,
                    'title' => $session->movie->title,
                    'starts_at' => $session->starts_at->format('d.m H:i'),
                    'ends_at' => $session->ends_at->format('d.m H:i'),
                    'busy_until' => $session->busy_until->format('d.m H:i'),
                ])
                ->values(),
        ]);
    }

    /**
     * Data shared by the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(MovieSession $session, Request $request): array
    {
        $defaultStart = now()->addMinutes(30);

        $date = (string) $request->old(
            'date',
            $session->starts_at?->format('Y-m-d') ?? $defaultStart->toDateString(),
        );

        $day = Carbon::hasFormat($date, 'Y-m-d')
            ? Carbon::createFromFormat('Y-m-d', $date)->startOfDay()
            : now()->startOfDay();

        $hallId = (int) $request->old('hall_id', $session->hall_id);
        $hall = $hallId > 0 ? Hall::find($hallId) : null;

        return [
            'session' => $session,
            'movies' => $this->sessions->selectableMovies($session->movie_id),
            'halls' => $this->sessions->selectableHalls($session->hall_id),
            'hall' => $hall,
            'date' => $day->format('Y-m-d'),
            'date_label' => $day->format('d.m.Y'),
            'time' => $session->starts_at?->format('H:i') ?? $defaultStart->format('H:i'),
            'occupied' => $hall
                ? $this->sessions->occupiedOn($hall, $day, $session->exists ? $session : null)
                : collect(),
            'buffer' => MovieSession::BUFFER_MINUTES,
        ];
    }
}
