<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Models\Movie;
use App\Services\MovieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MovieController extends Controller
{
    public function __construct(private readonly MovieService $movies) {}

    /**
     * Movie catalogue.
     */
    public function index(): View
    {
        return view('movies.index', [
            'movies' => Movie::query()
                ->withCount('sessions')
                ->orderBy('title')
                ->paginate(10),
        ]);
    }

    /**
     * Form for a new movie.
     */
    public function create(): View
    {
        return view('movies.create', [
            'movie' => new Movie,
        ]);
    }

    /**
     * Store a new movie.
     */
    public function store(StoreMovieRequest $request): RedirectResponse
    {
        $movie = $this->movies->create($request->validated());

        return redirect()
            ->route('movies.show', $movie)
            ->with('status', "Фильм «{$movie->title}» добавлен.");
    }

    /**
     * Single movie page.
     */
    public function show(Movie $movie): View
    {
        return view('movies.show', [
            'movie' => $movie->loadCount('sessions'),
        ]);
    }

    /**
     * Form for editing a movie.
     */
    public function edit(Movie $movie): View
    {
        return view('movies.edit', [
            'movie' => $movie,
        ]);
    }

    /**
     * Update a movie.
     */
    public function update(UpdateMovieRequest $request, Movie $movie): RedirectResponse
    {
        $this->movies->update($movie, $request->validated());

        return redirect()
            ->route('movies.show', $movie)
            ->with('status', 'Изменения сохранены.');
    }

    /**
     * Delete a movie that has no screenings.
     */
    public function destroy(Movie $movie): RedirectResponse
    {
        $this->movies->delete($movie);

        return redirect()
            ->route('movies.index')
            ->with('status', "Фильм «{$movie->title}» удалён.");
    }
}
