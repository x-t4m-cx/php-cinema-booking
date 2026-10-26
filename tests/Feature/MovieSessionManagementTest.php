<?php

namespace Tests\Feature;

use App\Enums\MovieSessionStatus;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\MovieSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MovieSessionManagementTest extends TestCase
{
    use RefreshDatabase;

    private const DATE = '2026-12-01';

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-26 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function movie(int $duration = 120, bool $active = true, string $title = 'Дюна'): Movie
    {
        return Movie::create([
            'title' => $title,
            'duration_minutes' => $duration,
            'is_active' => $active,
        ]);
    }

    private function hall(string $name = 'Красный зал', bool $active = true): Hall
    {
        return Hall::create([
            'name' => $name,
            'rows_count' => 2,
            'seats_per_rows' => 2,
            'is_active' => $active,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Movie $movie, Hall $hall, string $time, string $date = self::DATE): array
    {
        return [
            'movie_id' => $movie->id,
            'hall_id' => $hall->id,
            'date' => $date,
            'time' => $time,
            'price' => '350.00',
        ];
    }

    private function makeSession(
        Movie $movie,
        Hall $hall,
        string $time,
        string $date = self::DATE,
        MovieSessionStatus $status = MovieSessionStatus::Scheduled,
    ): MovieSession {
        return MovieSession::create([
            'movie_id' => $movie->id,
            'hall_id' => $hall->id,
            'starts_at' => Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}"),
            'price' => '300.00',
            'status' => $status,
        ]);
    }

    public function test_manager_can_create_a_session(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();

        $this->actingAs(User::factory()->manager()->create());

        $this->get(route('sessions.create'))->assertOk()->assertSee('Новый сеанс');

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '10:00'))
            ->assertRedirect(route('sessions.index'));

        $session = MovieSession::firstOrFail();

        $this->assertSame('10:00', $session->starts_at->format('H:i'));
        $this->assertSame('12:00', $session->ends_at->format('H:i'));
        $this->assertSame('12:20', $session->busy_until->format('H:i'));
        $this->assertSame(MovieSessionStatus::Scheduled, $session->status);
        $this->assertSame('350.00', $session->price);
    }

    public function test_sessions_in_the_same_hall_cannot_overlap(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();

        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '10:00'))
            ->assertRedirect(route('sessions.index'));

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '11:00'))
            ->assertSessionHasErrors('time');

        $this->assertDatabaseCount('movie_sessions', 1);
    }

    public function test_same_time_in_different_halls_is_allowed(): void
    {
        $movie = $this->movie();
        $first = $this->hall('Красный зал');
        $second = $this->hall('Синий зал');

        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('sessions.store'), $this->payload($movie, $first, '10:00'))
            ->assertRedirect(route('sessions.index'));
        $this->post(route('sessions.store'), $this->payload($movie, $second, '10:00'))
            ->assertRedirect(route('sessions.index'));

        $this->assertDatabaseCount('movie_sessions', 2);
    }

    public function test_session_can_start_right_after_the_buffer(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();

        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '10:00'))
            ->assertRedirect(route('sessions.index'));

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '12:20'))
            ->assertRedirect(route('sessions.index'));

        $this->assertDatabaseCount('movie_sessions', 2);
    }

    public function test_session_cannot_start_inside_the_buffer(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();

        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '10:00'))
            ->assertRedirect(route('sessions.index'));

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '12:19'))
            ->assertSessionHasErrors('time');

        $this->assertDatabaseCount('movie_sessions', 1);
    }

    public function test_session_crossing_midnight_is_checked(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();
        $nextDay = Carbon::parse(self::DATE)->addDay()->toDateString();

        $this->makeSession($movie, $hall, '23:30');

        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '01:30', $nextDay))
            ->assertSessionHasErrors('time');

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '01:50', $nextDay))
            ->assertRedirect(route('sessions.index'));

        $this->assertDatabaseCount('movie_sessions', 2);
    }

    public function test_editing_a_session_does_not_conflict_with_itself(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();
        $session = $this->makeSession($movie, $hall, '10:00');

        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('sessions.update', $session), [
            'movie_id' => $movie->id,
            'hall_id' => $hall->id,
            'date' => self::DATE,
            'time' => '10:00',
            'price' => '400.00',
        ])->assertRedirect(route('sessions.index'));

        $this->assertSame('400.00', $session->fresh()->price);
    }

    public function test_editing_a_session_still_checks_other_sessions(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();
        $first = $this->makeSession($movie, $hall, '10:00');
        $second = $this->makeSession($movie, $hall, '14:00');

        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('sessions.update', $second), [
            'movie_id' => $movie->id,
            'hall_id' => $hall->id,
            'date' => self::DATE,
            'time' => '11:00',
            'price' => '400.00',
        ])->assertSessionHasErrors('time');

        $this->assertSame('14:00', $second->fresh()->starts_at->format('H:i'));
        $this->assertTrue($first->exists);
    }

    public function test_cancelled_session_frees_its_slot(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();
        $cancelled = $this->makeSession($movie, $hall, '10:00');

        $this->actingAs(User::factory()->admin()->create());

        $this->patch(route('sessions.cancel', $cancelled))
            ->assertRedirect(route('sessions.index'));

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '11:00'))
            ->assertRedirect(route('sessions.index'));

        $this->assertSame(MovieSessionStatus::Cancelled, $cancelled->fresh()->status);
        $this->assertDatabaseCount('movie_sessions', 2);
    }

    public function test_cancelled_session_cannot_be_edited(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();
        $session = $this->makeSession($movie, $hall, '10:00', status: MovieSessionStatus::Cancelled);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('sessions.edit', $session))
            ->assertForbidden();
    }

    public function test_withdrawn_movie_cannot_get_new_sessions(): void
    {
        $movie = $this->movie(active: false);
        $hall = $this->hall();

        $this->actingAs(User::factory()->manager()->create());

        $this->get(route('sessions.create'))
            ->assertOk()
            ->assertDontSee($movie->title);

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '10:00'))
            ->assertSessionHasErrors('movie_id');

        $this->assertDatabaseCount('movie_sessions', 0);
    }

    public function test_withdrawn_movie_keeps_existing_sessions(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();
        $this->makeSession($movie, $hall, '10:00');

        $movie->update(['is_active' => false]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('sessions.index'))
            ->assertOk()
            ->assertSee($movie->title)
            ->assertSee('активен');
    }

    public function test_closed_hall_cannot_get_new_sessions(): void
    {
        $movie = $this->movie();
        $hall = $this->hall(active: false);

        $this->actingAs(User::factory()->manager()->create());

        $this->get(route('sessions.create'))
            ->assertOk()
            ->assertDontSee($hall->name);

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '10:00'))
            ->assertSessionHasErrors('hall_id');

        $this->assertDatabaseCount('movie_sessions', 0);
    }

    public function test_availability_shows_busy_intervals_with_the_buffer(): void
    {
        $movie = $this->movie();
        $nightMovie = $this->movie(120, true, 'Ночной фильм');
        $hall = $this->hall();
        $previousDay = Carbon::parse(self::DATE)->subDay()->toDateString();

        $this->makeSession($nightMovie, $hall, '23:30', $previousDay);
        $this->makeSession($movie, $hall, '10:00');
        $this->makeSession($movie, $hall, '14:00', status: MovieSessionStatus::Cancelled);

        $this->actingAs(User::factory()->manager()->create())
            ->getJson(route('sessions.occupied', ['hall_id' => $hall->id, 'date' => self::DATE]))
            ->assertOk()
            ->assertJsonPath('date_label', '01.12.2026')
            ->assertJsonPath('buffer', MovieSession::BUFFER_MINUTES)
            ->assertJsonCount(2, 'occupied')
            ->assertJsonPath('occupied.0.title', 'Ночной фильм')
            ->assertJsonPath('occupied.0.starts_at', '30.11 23:30')
            ->assertJsonPath('occupied.0.ends_at', '01.12 01:30')
            ->assertJsonPath('occupied.0.busy_until', '01.12 01:50')
            ->assertJsonPath('occupied.1.title', $movie->title)
            ->assertJsonPath('occupied.1.busy_until', '01.12 12:20');
    }

    public function test_availability_excludes_the_session_being_edited(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();
        $session = $this->makeSession($movie, $hall, '10:00');

        $this->actingAs(User::factory()->manager()->create())
            ->getJson(route('sessions.occupied', [
                'hall_id' => $hall->id,
                'date' => self::DATE,
                'session_id' => $session->id,
            ]))
            ->assertOk()
            ->assertJsonCount(0, 'occupied');
    }

    public function test_buyer_cannot_manage_sessions(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();

        $this->actingAs(User::factory()->buyer()->create());

        $this->get(route('sessions.index'))->assertForbidden();
        $this->get(route('sessions.create'))->assertForbidden();
        $this->post(route('sessions.store'), $this->payload($movie, $hall, '10:00'))->assertForbidden();
        $this->getJson(route('sessions.occupied', ['hall_id' => $hall->id, 'date' => self::DATE]))
            ->assertForbidden();
    }

    public function test_guests_are_redirected_from_the_schedule(): void
    {
        $this->get(route('sessions.index'))->assertRedirect(route('login'));
        $this->get(route('sessions.create'))->assertRedirect(route('login'));
    }

    public function test_edit_page_shows_the_form_and_the_free_hall(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();
        $session = $this->makeSession($movie, $hall, '10:00');

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('sessions.edit', $session))
            ->assertOk()
            ->assertSee('Редактирование сеанса')
            ->assertSee('Занятость зала')
            ->assertSee('свободен');
    }

    public function test_conflict_error_shows_the_busy_interval_again(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();
        $this->makeSession($movie, $hall, '10:00');

        $this->actingAs(User::factory()->admin()->create());

        $this->from(route('sessions.create'))
            ->post(route('sessions.store'), $this->payload($movie, $hall, '11:00'))
            ->assertRedirect(route('sessions.create'))
            ->assertSessionHasErrors('time');

        $this->get(route('sessions.create'))
            ->assertOk()
            ->assertSee('Занятость зала')
            ->assertSee($hall->name)
            ->assertSee('12:20');
    }

    public function test_session_cannot_be_created_in_the_past(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();

        $this->actingAs(User::factory()->manager()->create());

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '10:00', '2026-10-25'))
            ->assertSessionHasErrors('time');

        $this->assertDatabaseCount('movie_sessions', 0);
    }

    public function test_session_cannot_start_too_close_to_now(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();

        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '10:10', '2026-10-26'))
            ->assertSessionHasErrors('time');

        $this->assertDatabaseCount('movie_sessions', 0);
    }

    public function test_session_can_start_after_the_lead_time(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();

        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('sessions.store'), $this->payload($movie, $hall, '10:15', '2026-10-26'))
            ->assertRedirect(route('sessions.index'));

        $this->assertDatabaseCount('movie_sessions', 1);
    }

    public function test_session_cannot_be_moved_to_the_past(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();
        $session = $this->makeSession($movie, $hall, '12:00');

        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('sessions.update', $session), [
            'movie_id' => $movie->id,
            'hall_id' => $hall->id,
            'date' => '2026-10-25',
            'time' => '10:00',
            'price' => '400.00',
        ])->assertSessionHasErrors('time');

        $this->assertSame('12:00', $session->fresh()->starts_at->format('H:i'));
    }

    public function test_finished_session_is_inactive(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();

        $session = MovieSession::create([
            'movie_id' => $movie->id,
            'hall_id' => $hall->id,
            'starts_at' => Carbon::parse('2026-10-26 07:00'),
            'price' => '300.00',
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('sessions.index'))
            ->assertOk()
            ->assertSee('завершён');

        $this->get(route('sessions.edit', $session))->assertForbidden();
    }

    public function test_sessions_link_is_visible_to_staff_only(): void
    {
        $this->actingAs(User::factory()->manager()->create())
            ->get(route('movies.index'))
            ->assertSee('Сеансы');

        $this->actingAs(User::factory()->buyer()->create())
            ->get(route('movies.index'))
            ->assertDontSee('Сеансы');
    }
}
