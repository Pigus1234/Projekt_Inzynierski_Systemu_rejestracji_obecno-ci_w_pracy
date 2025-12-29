<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministratorUsersAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_administrator_users_index(): void
    {
        $response = $this->get(route('administrator.users.index'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_standard_user_cannot_open_administrator_users_index(): void
    {
        $standardRole = Role::query()->create(['name' => 'Standard']);
        $standardUser = User::factory()->create(['role_id' => $standardRole->id]);

        $response = $this->actingAs($standardUser)->get(route('administrator.users.index'));

        $response->assertForbidden();
    }

    public function test_administrator_user_can_open_administrator_users_index(): void
    {
        $administratorRole = Role::query()->create(['name' => 'Administrator']);
        $administratorUser = User::factory()->create(['role_id' => $administratorRole->id]);

        $response = $this->actingAs($administratorUser)->get(route('administrator.users.index'));

        $response->assertOk();
        $response->assertSee('Użytkownicy systemu');
    }
}
