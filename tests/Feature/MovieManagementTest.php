<?php

namespace Tests\Feature;

use App\Models\Hall;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_movies_index_lists_movies(): void
    {
        Movie::create(['title' => 'Интерстеллар', 'duration_minutes' => 169]);

        $this->get(route('movies.index'))
            ->assertOk()
            ->assertSee('Интерстеллар')
            ->assertSee('2 ч 49 мин');
    }

    public function test_create_and_edit_pages_are_displayed(): void
    {
        $movie = Movie::create(['title' => 'Начало', 'duration_minutes' => 148]);

        $this->get(route('movies.create'))->assertOk()->assertSee('Новый фильм');
        $this->get(route('movies.edit', $movie))->assertOk()->assertSee('Редактирование фильма');
    }

    public function test_movie_can_be_stored(): void
    {
        $response = $this->post(route('movies.store'), [
            'title' => 'Дюна',
            'description' => 'Песчаная планета',
            'duration_minutes' => 155,
        ]);

        $movie = Movie::where('title', 'Дюна')->firstOrFail();

        $response->assertRedirect(route('movies.show', $movie));
        $this->assertSame(155, $movie->duration_minutes);
        $this->assertSame('Песчаная планета', $movie->description);
        $this->get(route('movies.show', $movie))->assertOk()->assertSee('Дюна');
    }

    public function test_movie_title_must_be_unique(): void
    {
        Movie::create(['title' => 'Дюна', 'duration_minutes' => 155]);

        $this->post(route('movies.store'), [
            'title' => 'Дюна',
            'duration_minutes' => 100,
        ])->assertSessionHasErrors('title');

        $this->assertDatabaseCount('movies', 1);
    }

    public function test_movie_can_be_updated(): void
    {
        $movie = Movie::create(['title' => 'Старое название', 'duration_minutes' => 100]);

        $this->put(route('movies.update', $movie), [
            'title' => 'Новое название',
            'description' => 'Описание',
            'duration_minutes' => 120,
        ])->assertRedirect(route('movies.show', $movie));

        $this->assertSame('Новое название', $movie->fresh()->title);
        $this->assertSame(120, $movie->fresh()->duration_minutes);
    }

    public function test_movie_with_sessions_cannot_be_deleted(): void
    {
        $movie = Movie::create(['title' => 'Фильм', 'duration_minutes' => 90]);
        $hall = Hall::create(['name' => 'Зал', 'rows_count' => 1, 'seats_per_rows' => 1]);

        $movie->sessions()->create([
            'hall_id' => $hall->id,
            'starts_at' => now()->addDay(),
            'price' => 300,
        ]);

        $this->delete(route('movies.destroy', $movie))->assertSessionHasErrors('movie');

        $this->assertDatabaseHas('movies', ['id' => $movie->id]);
    }

    public function test_movie_without_sessions_can_be_deleted(): void
    {
        $movie = Movie::create(['title' => 'Фильм', 'duration_minutes' => 90]);

        $this->delete(route('movies.destroy', $movie))
            ->assertRedirect(route('movies.index'));

        $this->assertDatabaseMissing('movies', ['id' => $movie->id]);
    }
}
