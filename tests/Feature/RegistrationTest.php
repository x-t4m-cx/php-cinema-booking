<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_is_available_to_guests(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Регистрация')
            ->assertDontSee('name="role"', false);
    }

    public function test_a_guest_can_register_and_becomes_a_buyer(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Анна',
            'email' => 'anna@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $user = User::where('email', 'anna@example.com')->firstOrFail();

        $response->assertRedirect(route('movies.index'));
        $this->assertSame(UserRole::Buyer, $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_role_sent_with_registration_is_ignored(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Хитрый гость',
            'email' => 'sneaky@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'role' => UserRole::Admin->value,
        ])->assertRedirect(route('movies.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'sneaky@example.com',
            'role' => UserRole::Buyer->value,
        ]);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'anna@example.com']);

        $this->post(route('register.store'), [
            'name' => 'Анна',
            'email' => 'anna@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Анна',
            'email' => 'anna@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'other-password',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_signed_in_user_is_redirected_away_from_registration(): void
    {
        $this->actingAs(User::factory()->buyer()->create())
            ->get(route('register'))
            ->assertRedirect();
    }
}
