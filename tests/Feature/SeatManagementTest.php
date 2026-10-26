<?php

namespace Tests\Feature;

use App\Models\Hall;
use App\Models\Movie;
use App\Models\Seat;
use App\Models\User;
use App\Services\HallService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    private function hall(int $rows = 2, int $seats = 3): Hall
    {
        $hall = Hall::create(['name' => 'Зал', 'rows_count' => $rows, 'seats_per_rows' => $seats]);
        app(HallService::class)->createMissingSeats($hall);

        return $hall;
    }

    public function test_seat_create_page_is_displayed(): void
    {
        $hall = $this->hall();

        $this->get(route('halls.seats.create', $hall))
            ->assertOk()
            ->assertSee('Новое место')
            ->assertSee('Зал «Зал»', false);
    }

    public function test_seat_can_be_stored_in_the_hall(): void
    {
        $hall = $this->hall(2, 2);
        $free = $hall->seats()->where('row_number', 1)->where('seat_number', 1)->firstOrFail();
        $free->delete();

        $response = $this->post(route('halls.seats.store', $hall), [
            'row_number' => 1,
            'seat_number' => 1,
            'is_active' => '1',
        ]);

        $created = Seat::where('hall_id', $hall->id)
            ->where('row_number', 1)
            ->where('seat_number', 1)
            ->firstOrFail();

        $response->assertRedirect(route('seats.show', $created));
        $this->assertTrue($created->is_active);
    }

    public function test_duplicate_position_is_rejected(): void
    {
        $hall = $this->hall(2, 2);

        $this->post(route('halls.seats.store', $hall), [
            'row_number' => 1,
            'seat_number' => 1,
            'is_active' => '1',
        ])->assertSessionHasErrors('row_number');
    }

    public function test_seat_outside_the_hall_grid_is_rejected(): void
    {
        $hall = $this->hall(2, 3);

        $this->post(route('halls.seats.store', $hall), [
            'row_number' => 5,
            'seat_number' => 1,
            'is_active' => '1',
        ])->assertSessionHasErrors('row_number');
    }

    public function test_seat_page_and_edit_page_are_displayed(): void
    {
        $hall = $this->hall();
        $seat = $hall->seats()->firstOrFail();

        $this->get(route('seats.show', $seat))
            ->assertOk()
            ->assertSee('Ряд 1, место 1')
            ->assertSee('Бронирования места');

        $this->get(route('seats.edit', $seat))
            ->assertOk()
            ->assertSee('Редактирование места');
    }

    public function test_seat_can_be_updated_and_deactivated(): void
    {
        $hall = $this->hall(2, 2);
        $hall->seats()->where('row_number', 2)->where('seat_number', 1)->firstOrFail()->delete();

        $seat = $hall->seats()
            ->where('row_number', 1)
            ->where('seat_number', 2)
            ->firstOrFail();

        $this->put(route('seats.update', $seat), [
            'row_number' => 2,
            'seat_number' => 1,
            'is_active' => '0',
        ])->assertRedirect(route('seats.show', $seat));

        $this->assertSame(2, $seat->fresh()->row_number);
        $this->assertSame(1, $seat->fresh()->seat_number);
        $this->assertFalse($seat->fresh()->is_active);
    }

    public function test_booked_seat_cannot_be_deleted(): void
    {
        $hall = $this->hall(1, 1);
        $seat = $hall->seats()->firstOrFail();

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
        $booking->seats()->attach($seat);

        $this->delete(route('seats.destroy', $seat))->assertSessionHasErrors('seat');

        $this->assertDatabaseHas('seats', ['id' => $seat->id]);
    }

    public function test_free_seat_can_be_deleted_and_redirects_to_the_grid(): void
    {
        $hall = $this->hall(1, 2);
        $seat = $hall->seats()->orderBy('seat_number')->firstOrFail();

        $this->delete(route('seats.destroy', $seat))
            ->assertRedirect(route('halls.show', $hall));

        $this->assertDatabaseMissing('seats', ['id' => $seat->id]);
    }
}
