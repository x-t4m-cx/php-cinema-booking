<?php

namespace Tests\Feature;

use App\Enums\MovieSessionStatus;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\MovieSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SchedulePageTest extends TestCase
{
    use RefreshDatabase;

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

    private function movie(string $title = 'Дюна', int $duration = 120, bool $active = true): Movie
    {
        return Movie::create([
            'title' => $title,
            'duration_minutes' => $duration,
            'is_active' => $active,
        ]);
    }

    private function hall(string $name = 'Красный зал'): Hall
    {
        return Hall::create([
            'name' => $name,
            'rows_count' => 2,
            'seats_per_rows' => 2,
        ]);
    }

    private function makeSession(
        Movie $movie,
        Hall $hall,
        string $startsAt,
        MovieSessionStatus $status = MovieSessionStatus::Scheduled,
    ): MovieSession {
        return MovieSession::create([
            'movie_id' => $movie->id,
            'hall_id' => $hall->id,
            'starts_at' => Carbon::parse($startsAt),
            'price' => '350.00',
            'status' => $status,
        ]);
    }

    public function test_guests_see_today_schedule_by_default(): void
    {
        $today = $this->movie('Дюна');
        $tomorrow = $this->movie('Интерстеллар');
        $hall = $this->hall();

        $this->makeSession($today, $hall, '2026-10-26 14:00');
        $this->makeSession($tomorrow, $hall, '2026-10-27 15:00');

        $this->get(route('schedule.index'))
            ->assertOk()
            ->assertSee('Дюна')
            ->assertSee('14:00')
            ->assertSee('сегодня')
            ->assertDontSee('Интерстеллар')
            ->assertDontSee('15:00')
            ->assertSee(route('schedule.index', ['date' => '2026-10-27']), false);
    }

    public function test_date_switch_shows_only_that_day(): void
    {
        $movie = $this->movie('Дюна');
        $tomorrow = $this->movie('Интерстеллар');
        $hall = $this->hall();

        $this->makeSession($movie, $hall, '2026-10-26 14:00');
        $this->makeSession($tomorrow, $hall, '2026-10-27 15:00');

        $this->get(route('schedule.index', ['date' => '2026-10-27']))
            ->assertOk()
            ->assertSee('Интерстеллар')
            ->assertSee('15:00')
            ->assertDontSee('Дюна')
            ->assertDontSee('14:00');
    }

    public function test_past_and_cancelled_sessions_are_hidden(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();

        $this->makeSession($movie, $hall, '2026-10-26 08:00');
        $this->makeSession($movie, $hall, '2026-10-26 09:30');
        $this->makeSession($movie, $hall, '2026-10-26 12:00', MovieSessionStatus::Cancelled);
        $this->makeSession($movie, $hall, '2026-10-26 14:00');

        $this->get(route('schedule.index'))
            ->assertOk()
            ->assertSee('14:00')
            ->assertDontSee('08:00')
            ->assertDontSee('09:30')
            ->assertDontSee('12:00');
    }

    public function test_films_without_sessions_are_not_shown(): void
    {
        $shown = $this->movie('Дюна');
        $this->movie('Интерстеллар');
        $hall = $this->hall();

        $this->makeSession($shown, $hall, '2026-10-26 14:00');

        $this->get(route('schedule.index'))
            ->assertOk()
            ->assertSee('Дюна')
            ->assertDontSee('Интерстеллар');
    }

    public function test_sessions_are_grouped_by_film(): void
    {
        $dune = $this->movie('Дюна');
        $interstellar = $this->movie('Интерстеллар');
        $hall = $this->hall();

        $this->makeSession($dune, $hall, '2026-10-26 12:00');
        $this->makeSession($dune, $hall, '2026-10-26 18:00');
        $this->makeSession($interstellar, $hall, '2026-10-26 16:00');

        $this->get(route('schedule.index'))
            ->assertOk()
            ->assertSeeInOrder(['Дюна', '12:00', '18:00', 'Интерстеллар', '16:00'])
            ->assertSee('Красный зал')
            ->assertSee('350 ₽');
    }

    public function test_all_sessions_mode_groups_by_date_and_film(): void
    {
        $dune = $this->movie('Дюна');
        $interstellar = $this->movie('Интерстеллар');
        $hall = $this->hall();

        $this->makeSession($dune, $hall, '2026-10-26 14:00');
        $this->makeSession($interstellar, $hall, '2026-10-27 15:00');
        $this->makeSession($dune, $hall, '2026-10-27 20:00', MovieSessionStatus::Cancelled);

        $this->get(route('schedule.index', ['date' => 'all']))
            ->assertOk()
            ->assertSee('Все будущие сеансы с сегодняшнего дня.')
            ->assertSeeInOrder(['26.10.2026', 'Дюна', '14:00', '27.10.2026', 'Интерстеллар', '15:00'])
            ->assertDontSee('20:00')
            ->assertSee(route('schedule.index'), false);
    }

    public function test_empty_day_suggests_all_sessions(): void
    {
        $tomorrow = $this->movie('Интерстеллар');
        $hall = $this->hall();

        $this->makeSession($tomorrow, $hall, '2026-10-27 15:00');

        $this->get(route('schedule.index'))
            ->assertOk()
            ->assertSee('На эту дату сеансов нет, выберите другой день.')
            ->assertSee('Показать все сеансы');
    }

    public function test_fully_empty_schedule_has_its_own_message(): void
    {
        $this->get(route('schedule.index'))
            ->assertOk()
            ->assertSee('Расписание пока не опубликовано.');

        $this->get(route('schedule.index', ['date' => 'all']))
            ->assertOk()
            ->assertSee('Расписание пока не опубликовано.');
    }

    public function test_session_card_opens_the_booking_placeholder(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();
        $session = $this->makeSession($movie, $hall, '2026-10-26 14:00');

        $this->get(route('schedule.index'))
            ->assertOk()
            ->assertSee(route('bookings.create', $session), false);

        $this->get(route('bookings.create', $session))
            ->assertOk()
            ->assertSee('Бронирование')
            ->assertSee('Выбор мест и оплата появятся позже.');
    }

    public function test_cancelled_session_has_no_booking_page(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();
        $session = $this->makeSession($movie, $hall, '2026-10-26 14:00', MovieSessionStatus::Cancelled);

        $this->get(route('bookings.create', $session))->assertNotFound();
    }

    public function test_past_date_redirects_to_today(): void
    {
        $this->get(route('schedule.index', ['date' => '2026-10-25']))
            ->assertRedirect(route('schedule.index', ['date' => '2026-10-26']));
    }

    public function test_broken_date_redirects_to_the_default_schedule(): void
    {
        $this->get(route('schedule.index', ['date' => 'not-a-date']))
            ->assertRedirect(route('schedule.index'));
    }
}
