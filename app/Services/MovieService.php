<?php

namespace App\Services;

use App\Models\Movie;
use Illuminate\Validation\ValidationException;

/**
 * Movie catalogue.
 */
class MovieService
{
    /**
     * @param  array{title: string, description?: string|null, duration_minutes: int, is_active?: bool}  $data
     */
    public function create(array $data): Movie
    {
        return Movie::create($data);
    }

    /**
     * @param  array{title: string, description?: string|null, duration_minutes: int, is_active?: bool}  $data
     */
    public function update(Movie $movie, array $data): Movie
    {
        $movie->update($data);

        return $movie;
    }

    /**
     * Delete a movie that has no screenings yet.
     *
     * @throws ValidationException
     */
    public function delete(Movie $movie): void
    {
        if ($movie->sessions()->exists()) {
            throw ValidationException::withMessages([
                'movie' => 'Фильм нельзя удалить: для него уже созданы сеансы.',
            ]);
        }

        $movie->delete();
    }
}
