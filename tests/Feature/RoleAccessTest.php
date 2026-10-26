<?php

namespace Tests\Feature;

use App\Models\Hall;
use App\Models\Movie;
use App\Models\User;
use App\Services\HallService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function movie(): Movie
    {
        return Movie::create(['title' => 'Дюна', 'duration_minutes' => 155]);
    }

    private function hall(): Hall
    {
        $hall = Hall::create(['name' => 'Красный зал', 'rows_count' => 1, 'seats_per_rows' => 2]);
        app(HallService::class)->createMissingSeats($hall);

        return $hall;
    }

    public function test_guests_can_browse_the_catalogue(): void
    {
        $movie = $this->movie();
        $hall = $this->hall();
        $seat = $hall->seats()->firstOrFail();

        $this->get(route('movies.index'))->assertOk()->assertSee('Дюна');
        $this->get(route('movies.show', $movie))->assertOk();
        $this->get(route('halls.index'))->assertOk()->assertSee('Красный зал');
        $this->get(route('halls.show', $hall))->assertOk();
        $this->get(route('seats.show', $seat))->assertOk();
    }

    public function test_guests_are_redirected_to_login_before_managing_anything(): void
    {
        $hall = $this->hall();

        $this->get(route('movies.create'))->assertRedirect(route('login'));
        $this->get(route('halls.create'))->assertRedirect(route('login'));
        $this->get(route('halls.seats.create', $hall))->assertRedirect(route('login'));

        $this->post(route('movies.store'), [
            'title' => 'Фильм',
            'duration_minutes' => 90,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('movies', 0);
    }

    public function test_buyer_cannot_create_films(): void
    {
        $this->actingAs(User::factory()->buyer()->create());

        $this->get(route('movies.create'))->assertForbidden();

        $this->post(route('movies.store'), [
            'title' => 'Фильм',
            'duration_minutes' => 90,
        ])->assertForbidden();

        $this->assertDatabaseCount('movies', 0);
    }

    public function test_buyer_cannot_manage_halls_and_seats(): void
    {
        $hall = $this->hall();
        $seat = $hall->seats()->firstOrFail();

        $this->actingAs(User::factory()->buyer()->create());

        $this->get(route('halls.create'))->assertForbidden();
        $this->put(route('halls.update', $hall), [
            'name' => 'Новый зал',
            'rows_count' => 2,
            'seats_per_rows' => 2,
        ])->assertForbidden();
        $this->get(route('seats.edit', $seat))->assertForbidden();
        $this->delete(route('seats.destroy', $seat))->assertForbidden();

        $this->assertSame('Красный зал', $hall->fresh()->name);
        $this->assertDatabaseHas('seats', ['id' => $seat->id]);
    }

    public function test_buyer_sees_no_management_buttons(): void
    {
        $this->movie();

        $this->actingAs(User::factory()->buyer()->create())
            ->get(route('movies.index'))
            ->assertOk()
            ->assertSee('Просмотр')
            ->assertDontSee('Добавить фильм')
            ->assertDontSee('Изменить')
            ->assertDontSee('Удалить');
    }

    public function test_manager_can_create_films(): void
    {
        $this->actingAs(User::factory()->manager()->create());

        $this->get(route('movies.create'))->assertOk()->assertSee('Новый фильм');

        $this->post(route('movies.store'), [
            'title' => 'Интерстеллар',
            'description' => 'Полет к другим галактикам',
            'duration_minutes' => 169,
        ])->assertRedirect();

        $this->assertDatabaseHas('movies', ['title' => 'Интерстеллар']);
    }

    public function test_manager_cannot_edit_or_delete_films(): void
    {
        $movie = $this->movie();

        $this->actingAs(User::factory()->manager()->create());

        $this->get(route('movies.edit', $movie))->assertForbidden();
        $this->put(route('movies.update', $movie), [
            'title' => 'Другое название',
            'duration_minutes' => 100,
        ])->assertForbidden();
        $this->delete(route('movies.destroy', $movie))->assertForbidden();

        $this->assertSame('Дюна', $movie->fresh()->title);
    }

    public function test_manager_cannot_manage_halls(): void
    {
        $hall = $this->hall();

        $this->actingAs(User::factory()->manager()->create());

        $this->get(route('halls.create'))->assertForbidden();
        $this->get(route('halls.edit', $hall))->assertForbidden();
    }

    public function test_manager_sees_only_the_create_button(): void
    {
        $this->movie();

        $this->actingAs(User::factory()->manager()->create())
            ->get(route('movies.index'))
            ->assertOk()
            ->assertSee('Добавить фильм')
            ->assertDontSee('Изменить')
            ->assertDontSee('Удалить');
    }

    public function test_admin_sees_all_management_buttons(): void
    {
        $this->movie();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('movies.index'))
            ->assertOk()
            ->assertSee('Добавить фильм')
            ->assertSee('Изменить')
            ->assertSee('Удалить');
    }

    public function test_buyer_opens_seats_instead_of_the_edit_form(): void
    {
        $hall = $this->hall();
        $seat = $hall->seats()->firstOrFail();

        $this->actingAs(User::factory()->buyer()->create())
            ->get(route('halls.show', $hall))
            ->assertOk()
            ->assertSee(route('seats.show', $seat), false)
            ->assertDontSee(route('seats.edit', $seat), false);
    }

    public function test_login_page_is_available_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Вход в панель')
            ->assertSee('Email');
    }

    public function test_user_can_sign_in(): void
    {
        $user = User::factory()->manager()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('movies.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->buyer()->create();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_sign_out(): void
    {
        $this->actingAs(User::factory()->buyer()->create())
            ->post(route('logout'))
            ->assertRedirect(route('movies.index'));

        $this->assertGuest();
    }
}
