<?php

namespace Tests\Feature;

use App\Models\Hall;
use App\Models\Movie;
use App\Models\User;
use App\Services\HallService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HallManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_halls_index_shows_existing_halls(): void
    {
        Hall::create(['name' => 'Красный зал', 'rows_count' => 2, 'seats_per_rows' => 3]);

        $this->get(route('halls.index'))
            ->assertOk()
            ->assertSee('Красный зал')
            ->assertSee('Добавить зал');
    }

    public function test_create_page_is_displayed(): void
    {
        $this->get(route('halls.create'))
            ->assertOk()
            ->assertSee('Новый зал');
    }

    public function test_storing_a_hall_generates_the_seat_grid(): void
    {
        $response = $this->post(route('halls.store'), [
            'name' => 'Синий зал',
            'rows_count' => 3,
            'seats_per_rows' => 4,
        ]);

        $hall = Hall::where('name', 'Синий зал')->firstOrFail();

        $response->assertRedirect(route('halls.show', $hall));
        $this->assertDatabaseCount('seats', 12);
        $this->assertDatabaseHas('seats', [
            'hall_id' => $hall->id,
            'row_number' => 3,
            'seat_number' => 4,
            'is_active' => true,
        ]);
    }

    public function test_hall_name_is_required(): void
    {
        $this->post(route('halls.store'), [
            'name' => '',
            'rows_count' => 2,
            'seats_per_rows' => 3,
        ])->assertSessionHasErrors('name');

        $this->assertDatabaseCount('halls', 0);
    }

    public function test_hall_page_renders_the_seat_grid(): void
    {
        $hall = Hall::create(['name' => 'Зелёный зал', 'rows_count' => 2, 'seats_per_rows' => 2]);
        app(HallService::class)->createMissingSeats($hall);

        $seat = $hall->seats()->orderBy('row_number')->orderBy('seat_number')->firstOrFail();

        $this->get(route('halls.show', $hall))
            ->assertOk()
            ->assertSee('Сетка мест')
            ->assertSee('Ряд 1, место 1', false)
            ->assertSee(route('seats.edit', $seat), false);
    }

    public function test_changing_hall_size_resizes_the_seat_grid(): void
    {
        $hall = Hall::create(['name' => 'Зал', 'rows_count' => 2, 'seats_per_rows' => 2]);
        app(HallService::class)->createMissingSeats($hall);

        $this->assertSame(4, $hall->seats()->count());

        $this->put(route('halls.update', $hall), [
            'name' => 'Зал',
            'rows_count' => 3,
            'seats_per_rows' => 2,
        ])->assertRedirect(route('halls.show', $hall));

        $this->assertSame(6, $hall->seats()->count());
        $this->assertSame(3, $hall->fresh()->rows_count);
    }

    public function test_shrinking_a_hall_removes_seats_that_no_longer_fit(): void
    {
        $hall = Hall::create(['name' => 'Зал', 'rows_count' => 3, 'seats_per_rows' => 3]);
        app(HallService::class)->createMissingSeats($hall);

        $this->put(route('halls.update', $hall), [
            'name' => 'Зал',
            'rows_count' => 2,
            'seats_per_rows' => 2,
        ])->assertRedirect(route('halls.show', $hall));

        $this->assertSame(4, $hall->seats()->count());
    }

    public function test_hall_with_bookings_cannot_be_deleted(): void
    {
        $hall = Hall::create(['name' => 'Зал', 'rows_count' => 1, 'seats_per_rows' => 1]);
        app(HallService::class)->createMissingSeats($hall);

        $movie = Movie::create(['title' => 'Фильм', 'duration_minutes' => 90]);
        $session = $movie->sessions()->create([
            'hall_id' => $hall->id,
            'starts_at' => now()->addDay(),
            'price' => 300,
        ]);
        $booking = $session->bookings()->create([
            'customer_name' => 'Гость',
            'customer_email' => 'guest@example.com',
            'total_amount' => 300,
        ]);
        $booking->seats()->attach($hall->seats()->firstOrFail());

        $this->delete(route('halls.destroy', $hall))->assertSessionHasErrors('hall');

        $this->assertDatabaseHas('halls', ['id' => $hall->id]);
    }

    public function test_empty_hall_can_be_deleted(): void
    {
        $hall = Hall::create(['name' => 'Зал', 'rows_count' => 1, 'seats_per_rows' => 2]);
        app(HallService::class)->createMissingSeats($hall);

        $this->delete(route('halls.destroy', $hall))
            ->assertRedirect(route('halls.index'));

        $this->assertDatabaseMissing('halls', ['id' => $hall->id]);
        $this->assertDatabaseCount('seats', 0);
    }
}
