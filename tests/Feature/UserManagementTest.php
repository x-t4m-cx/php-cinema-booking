<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
        $this->get(route('users.create'))->assertRedirect(route('login'));
    }

    public function test_buyer_and_manager_cannot_manage_users(): void
    {
        $this->actingAs(User::factory()->buyer()->create());
        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('users.create'))->assertForbidden();

        $this->actingAs(User::factory()->manager()->create());
        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('users.create'))->assertForbidden();

        $this->post(route('users.store'), [
            'name' => 'Новый менеджер',
            'email' => 'new-manager@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertForbidden();
    }

    public function test_admin_sees_users_with_their_roles(): void
    {
        $manager = User::factory()->manager()->create(['name' => 'Иван Менеджеров']);
        $buyer = User::factory()->buyer()->create(['name' => 'Анна Покупателева']);

        $this->actingAs($this->admin())
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee($manager->name)
            ->assertSee($buyer->name)
            ->assertSee('Администратор')
            ->assertSee('Менеджер')
            ->assertSee('Покупатель')
            ->assertSee('не изменяется');
    }

    public function test_admin_can_create_a_manager(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('users.create'))
            ->assertOk()
            ->assertSee('Новый менеджер')
            ->assertDontSee('name="role"', false);

        $this->post(route('users.store'), [
            'name' => 'Новый менеджер',
            'email' => 'new-manager@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'new-manager@example.com',
            'role' => UserRole::Manager->value,
        ]);
    }

    public function test_role_sent_when_creating_a_user_is_ignored(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('users.store'), [
            'name' => 'Хитрый администратор',
            'email' => 'sneaky@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'role' => UserRole::Admin->value,
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'sneaky@example.com',
            'role' => UserRole::Manager->value,
        ]);
    }

    public function test_admin_can_promote_a_buyer_and_demote_a_manager(): void
    {
        $buyer = User::factory()->buyer()->create();
        $manager = User::factory()->manager()->create();

        $this->actingAs($this->admin());

        $this->patch(route('users.update', $buyer), ['role' => UserRole::Manager->value])
            ->assertRedirect(route('users.index'));
        $this->assertSame(UserRole::Manager, $buyer->fresh()->role);

        $this->patch(route('users.update', $manager), ['role' => UserRole::Buyer->value])
            ->assertRedirect(route('users.index'));
        $this->assertSame(UserRole::Buyer, $manager->fresh()->role);
    }

    public function test_admin_role_cannot_be_assigned_by_update(): void
    {
        $buyer = User::factory()->buyer()->create();

        $this->actingAs($this->admin())
            ->from(route('users.index'))
            ->patch(route('users.update', $buyer), ['role' => UserRole::Admin->value])
            ->assertRedirect(route('users.index'))
            ->assertSessionHasErrors('role');

        $this->assertSame(UserRole::Buyer, $buyer->fresh()->role);
    }

    public function test_role_of_an_administrator_cannot_be_changed(): void
    {
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($this->admin())
            ->patch(route('users.update', $otherAdmin), ['role' => UserRole::Buyer->value])
            ->assertForbidden();

        $this->assertSame(UserRole::Admin, $otherAdmin->fresh()->role);
    }

    public function test_panel_has_no_administrator_option(): void
    {
        User::factory()->buyer()->create();

        $this->actingAs($this->admin())
            ->get(route('users.index'))
            ->assertOk()
            ->assertDontSee('value="admin"', false);
    }

    public function test_user_management_link_is_visible_only_to_the_admin(): void
    {
        $this->actingAs(User::factory()->manager()->create())
            ->get(route('movies.index'))
            ->assertDontSee('Пользователи');

        $this->actingAs($this->admin())
            ->get(route('movies.index'))
            ->assertSee('Пользователи');
    }
}
